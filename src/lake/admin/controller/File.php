<?php

namespace lake\admin\controller;

use lake\admin\model\Attachment as AttachmentModel;
use lake\admin\service\Upload as UploadService;
use lake\admin\service\Attachment as AttachmentService;
use lake\admin\facade\Admin as AdminFacade;

/**
 * 文件上传
 *
 * @create 2020-8-26
 * @author deatil
 */
class File extends Base
{
    /**
     * 附件列表
     *
     * @create 2020-8-27
     * @author deatil
     */
    public function lists()
    {
        $limit = $this->request->param('limit/d', 10);
        $page = $this->request->param('page/d', 1);
        $map = $this->buildparams();
        
        if (!env('admin_is_root')) {
            $map[] = ['type', '=', 'admin'];
            $map[] = ['type_id', '=', env('admin_id')];
        }
        
        $list = AttachmentModel::where($map)
            ->page($page, $limit)
            ->order('create_time desc')
            ->select()
            ->toArray();
        if (!empty($list)) {
            foreach ($list as $k => &$v) {
                $v['path'] = $v['driver'] == 'local' ? config('app.upload_url') . $v['path'] : $v['path'];
            }
            unset($v);
        }
        
        $total = AttachmentModel::where($map)->count();
        $result = [
            "code" => 0, 
            "count" => $total, 
            "data" => $list,
        ];
        
        return $this->json($result);
    }
    
    /**
     * 附件上传
     *
     * @create 2020-8-26
     * @author deatil
     */
    public function upload(
        $dir = '', 
        $from = '', 
        $module = '', 
        $thumb = 0, 
        $thumbsize = '', 
        $thumbtype = '', 
        $watermark = 1, 
        $sizelimit = -1, 
        $extlimit = ''
    ) {
        $UploadService = (new UploadService);
        
        $admin_id = AdminFacade::getLoginUserInfo('id');
        return $UploadService->setTypeInfo('admin', $admin_id)
            ->save($dir, $from, $module, $thumb, $thumbsize, $thumbtype, $watermark, $sizelimit, $extlimit);
    }

    /**
     * html代码远程图片本地化
     *
     * @create 2020-8-26
     * @author deatil
     */
    public function getUrlFile()
    {
        $AttachmentService = (new AttachmentService);
        
        $admin_id = AdminFacade::getLoginUserInfo('id');
        return $AttachmentService->setTypeInfo('admin', $admin_id)
            ->getUrlFile();
    }

}
