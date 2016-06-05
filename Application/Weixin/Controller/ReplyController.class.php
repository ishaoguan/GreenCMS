<?php
/**
 * Created by GreenStudio GCS Dev Team.
 * File: ReplyController.class.php
 * User: Timothy Zhang
 * Date: 14-2-20
 * Time: 下午5:31
 */

namespace Weixin\Controller;


use Common\Util\GreenPage;
use Think\Upload;
use Weixin\Util\Media;

class ReplyController extends WeixinBaseController
{

    public function index()
    {
        $page = I('get.page', get_opinion('PAGER'));


        $count = D('Weixinre')->count();
        $Page = new GreenPage($count, $page); // 实例化分页类 传入总记录数
        $pager_bar = $Page->show();
        $limit = $Page->firstRow . ',' . $Page->listRows;


        $weixinre = D('Weixinre')->limit($limit)->select();

        $this->assign('pager', $pager_bar);
        $this->assign('weixinre', $weixinre);

        $this->display();
    }

    public function text()
    {
        $this->assign('form_action', U('Weixin/Reply/textAddHandle'));
        $this->assign('action_name', '添加');

        $this->display();

    }

    public function textAddHandle()
    {
        $data = I('post.', '', null);
        $data['type'] = "text";
        $res = D('Weixinre')->data($data)->add();

        if ($res) {
            $this->success('添加成功', U('Weixin/Reply/index'));
        } else {
            $this->error('添加失败');
        }
    }

    public function textEdit($id)
    {

        $this->assign('action', '编辑回复');
        $this->assign('action_url', U('Weixin/Reply/textEdit', array('id' => $id)));

        $item = D('Weixinre')->where(array('wx_re_id' => $id))->find();
        $this->assign('item', $item);
        $this->assign('form_action', U('Weixin/Reply/textEditHandle', array('id' => $id)));

        $this->assign('action_name', '编辑');
        $this->display('Reply/text');
    }

    public function textEditHandle($id)
    {
        $data = I('post.');
        $data['type'] = "text";
        $res = D('Weixinre')->where(array('wx_re_id' => $id))->data($data)->save();

        if ($res) {
            $this->success('编辑成功', U('Weixin/Reply/index'));
        } else {
            $this->error('编辑失败或者是没有改变');
        }
    }


    public function pic()
    {

        $this->assign('form_action', U('Weixin/Reply/picAddHandle'));
        $this->assign('action_name', '添加');
        $this->display();

    }

    public function picAddHandle()
    {

        $config = array(
            "savePath" => 'Weixin/',
            "maxSize" => 1000000, // 单位B
            "exts" => array('jpg', 'jpeg'),
            "subName" => array('date', 'Y/m-d'),
        );
        $upload = new Upload($config);
        $info = $upload->upload();

        if (!$info) { // 上传错误提示错误信息
            $this->error($upload->getError());
        } else { // 上传成功 获取上传文件信息

            //   $file_path_full = Upload_PATH . $info['img']['savepath'] . $info['img']['savename'];
            $file_path_full = $info['img']['fullpath'];

            if (!defined('SAE_TMP_PATH')) {
                // 非SAE环境中
                $image = new \Think\Image();
                $image->open($file_path_full);
                $image->thumb(150, 150)->save($file_path_full);
                $img_url = "http://" . $_SERVER['SERVER_NAME'] . str_replace('index.php', '', __APP__) . $file_path_full;
            } else {
                // SAE环境中
                $img_url = $file_path_full;
            }


            $Media = new Media();
            $res = $Media->upload($file_path_full, "image");


            $data['type'] = $res['type'];
            $data['mediaId'] = $res['media_id'];
            $data['picurl'] = $img_url;
            $res = D('Weixinre')->data($data)->add();

            if ($res) {
                $this->success('上传成功！', U('Weixin/Reply/index'));
            }
        };


    }


    public function news()
    {
        $this->assign('imgurl', __ROOT__ . '/Public/share/img/no+image.gif');
        $this->assign('form_action', U('Weixin/Reply/newsAddHandle'));
        $this->assign('action_name', '添加');
        $this->display();

    }

    public function newsEdit($id)
    {
        $this->assign('action', '编辑回复');
        $this->assign('action_url', U('Weixin/Reply/textEdit', array('id' => $id)));

        $item = D('Weixinre')->where(array('wx_re_id' => $id))->find();
        $this->assign('imgurl', $item['picurl']);

        $this->assign('item', $item);
        $this->assign('form_action', U('Weixin/Reply/newsEditHandle', array('id' => $id)));

        $this->assign('action_name', '编辑');
        $this->display('Reply/news');

    }


    public function newsEditHandle($id)
    {


        $data = I('post.', '', null);
        $data['type'] = "news";
        if ($_FILES['img']['size'] != 0) {
            $config = array(
                "savePath" => 'Weixin/',
                "maxSize" => 1000000, // 单位B
                "exts" => array('jpg', 'gif', 'png', 'jpeg'),
                "subName" => array('date', 'Y/m-d'),
            );
            $upload = new Upload($config);
            $info = $upload->upload();

            if (!$info) { // 上传错误提示错误信息
                $this->error($upload->getError());
            } else { // 上传成功 获取上传文件信息

                $file_path_full = $info['img']['fullpath'];


                $img_url = "http://" . $_SERVER['SERVER_NAME'] . str_replace('index.php', '', __APP__) . $file_path_full;
                unset($data['img']);
                $data['picurl'] = $img_url;

            };


        }


        $res = D('Weixinre')->where(array('wx_re_id' => $id))->data($data)->save();

        if ($res) {
            $this->success('编辑成功', U('Weixin/Reply/index'));
        } else {
            $this->error('编辑失败或者是没有改变');
        }

    }

    public function newsAddHandle()
    {
        $data = I('post.');
        $data['type'] = "news";

        $config = array(
            "savePath" => 'Weixin/',
            "maxSize" => 1000000, // 单位B
            "exts" => array('jpg', 'gif', 'png', 'jpeg'),
            "subName" => array('date', 'Y/m-d'),
        );
        $upload = new Upload($config);
        $info = $upload->upload();

        if (!$info) { // 上传错误提示错误信息
            $this->error($upload->getError());
        } else { // 上传成功 获取上传文件信息

            $file_path_full = $info['img']['fullpath'];


            $img_url = "http://" . $_SERVER['SERVER_NAME'] . str_replace('index.php', '', __APP__) . $file_path_full;
            unset($data['img']);
            $data['picurl'] = $img_url;

        };


        $res = D('Weixinre')->data($data)->add();

        if ($res) {
            $this->success('添加成功', U('Weixin/Reply/index'));
        } else {
            $this->error('添加失败');
        }

    }

    public function del($id)
    {
        $res = D('Weixinre')->where(array('wx_re_id' => $id))->delete();

        if ($res) {
            $this->success('删除成功', U('Weixin/Reply/index'));
        } else {
            $this->error('删除失败');
        }

    }

}