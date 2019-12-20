<?php
//模块管理
global $_GPC, $_W;
$action = $_GPC['action']?$_GPC['action']:'module_panel';
$do     = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
$do     = in_array($do, array('display', 'post', 'delete','move_item')) ? $do : 'display';
$pindex = max(1, intval($_GPC['page']));
$psize  = 10;
try {
    if ($action=='slideshow') {
        if ($do == 'display') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $slideshow_id => $displayorder) {
                    $update = array('displayorder' => $displayorder);
                    update('yuexiage_travelmall_module', $update, array('mid' => $slideshow_id));
                }
                message('幻灯片排序更新成功！', 'refresh', 'success');
            }

            $params     = [':is_del'=>0];
            $condition  = " and is_del = :is_del";
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND name LIKE :keyword";
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }

            $params[':uniacid'] = $_W['uniacid'];
            $modules = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_module_slideshow')." WHERE uniacid = :uniacid $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
            $total   = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_module_slideshow') . " WHERE uniacid = :uniacid $condition ",$params);
            $pager   = pagination($total, $pindex, $psize);
        }elseif ($do == 'post') {
            $slideshow_id = $_GPC['id'];
            if(!empty($slideshow_id)) {
                $module_post = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_module_slideshow')." WHERE slideshow_id = :slideshow_id AND uniacid = :uniacid and is_del = :is_del",array(
                    ':slideshow_id' => $slideshow_id,
                    'uniacid'       => $_W['uniacid'],
                    ':is_del'       => 0,
                ));
                $module_post['img'] = iunserializer($module_post['img']);
                $module_post['lnk'] = iunserializer($module_post['lnk']);
                if(empty($module_post)) {
                    message('幻灯片不存在或已删除', '', 'error');
                }
            } else {
                $country = array(
                    'displayorder' => 0
                );
            }
            if (checksubmit('submit')) {
                pdo_begin();
                if (empty($_GPC['name'])) {
                    message('抱歉，请输入国家名称！');
                }
                $data = array(
                    'uniacid'       => $_W['uniacid'],
                    'name'          => $_GPC['name'],
                    'displayorder'  => intval($_GPC['displayorder']),
                    'enabled'       => intval($_GPC['enabled']),
                    'pid'           => intval($_GPC['pid']),
                    'img'           => iserializer($_GPC['img']),
                    'lnk'           => iserializer($_GPC['lnk'])
                );
                if (!empty($slideshow_id)) {
                    update('yuexiage_travelmall_module_slideshow', $data, array('slideshow_id' => $slideshow_id));
                    $data = array(
                        'uniacid'       => $_W['uniacid'],
                        'name'          => $_GPC['name'],
                        'displayorder'  => intval($_GPC['displayorder']),
                        'enabled'       => intval($_GPC['enabled']),
                        'pid'           => intval($_GPC['pid']),
                        'type'          => 'slideshow',
                        'mid'           => $slideshow_id
                    );
                    update('yuexiage_travelmall_module', $data, array('mid' => $slideshow_id,'type'=>'slideshow'));
                } else {
                    $data['slideshow_id'] = uuid();
                    insert('yuexiage_travelmall_module_slideshow', $data);
                    $data = array(
                        'uniacid'       => $_W['uniacid'],
                        'name'          => $_GPC['name'],
                        'displayorder'  => intval($_GPC['displayorder']),
                        'enabled'       => intval($_GPC['enabled']),
                        'pid'           => intval($_GPC['pid']),
                        'type'          => 'slideshow',
                        'mid'           => $data['slideshow_id']
                    );
                    insert('yuexiage_travelmall_module', $data);
                }
                pdo_commit();
                message('更新幻灯片成功！', $this->createWebUrl('module', array('op' => 'display','action'=>'slideshow')), 'success');
            }
        }elseif ($do == 'delete') {
            $slideshow_id = $_GPC['id'];
            if(!empty($id)) {
                $module = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_module_slideshow')." WHERE slideshow_id = :slideshow_id AND uniacid = :uniacid and is_del = :is_del",array(
                    ':slideshow_id' => $slideshow_id,
                    'uniacid'       => $_W['uniacid'],
                    ':is_del'       => 0,
                ));
                if(empty($module)) {
                    message('轮播图不存在或已删除', '', 'error');
                }
            }else{
                $module = array(
                    'displayorder' => 0
                );
            }
            pdo_begin();
            update('yuexiage_travelmall_module_slideshow', ['is_del'=>1], array('slideshow_id' => $slideshow_id));
            update('yuexiage_travelmall_module', ['is_del'=>1], array('mid' => $slideshow_id));
            pdo_commit();
            message('删除幻灯片成功！', $this->createWebUrl('module', array('op' => 'display','action'=>'slideshow')), 'success');
        }
    }elseif ($action=='filter') {
        if ($do == 'display') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $filter_id => $displayorder) {
                    $update = array('displayorder' => $displayorder);
                    update('yuexiage_travelmall_module', $update, array('mid' => $filter_id));
                }
                message('过滤排序更新成功！', 'refresh', 'success');
            }

            $params     = [':is_del'=>0];
            $condition  = " and is_del = :is_del";
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND name LIKE :keyword";
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }
            $params[':uniacid'] = $_W['uniacid'];
            $modules    = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_module_filter')." WHERE uniacid = :uniacid $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
            $total      = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_module_filter') . " WHERE uniacid = :uniacid $condition ",$params);
            $pager      = pagination($total, $pindex, $psize);
        }elseif ($do == 'post') {
            $filter_id  = $_GPC['id'];
            //过滤条件
            $filter = array(
                '1'=>['id'=>1,'name'=>'目的地'],
                '2'=>['id'=>2,'name'=>'行程时间'],
                '3'=>['id'=>3,'name'=>'出发城市'],
                '4'=>['id'=>4,'name'=>'标签'],
                '5'=>['id'=>5,'name'=>'主题'],
            );
            //目的地
            $citys =  com_load_cache(array(
                'cache_key'=>'city',
            ));
            $citys = array_values($citys);

            //标签
            $tabs     =  com_load_cache(array(
                'cache_key'=>'tab',
            ));

            //查询主题
            $themes     =  com_load_cache(array(
                'cache_key'=>'theme',
            ));

            $filter_details = array(
                '1'=>$citys,
                '2'=>array(
                    ['id'=>1,'name'=>'1-5天'],
                    ['id'=>2,'name'=>'6-9天'],
                    ['id'=>3,'name'=>'10-15天'],
                    ['id'=>4,'name'=>'16-20天'],
                    ['id'=>5,'name'=>'21天以上'],
                ),
                '3'=>$citys,
                '4'=>$tabs,
                '5'=>$themes
            );
            if(!empty($filter_id)) {
                $module_post        = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_module_filter')." WHERE filter_id = :filter_id AND uniacid = :uniacid and is_del = :is_del",array(
                    ':filter_id' =>$filter_id,
                    ':uniacid'   =>$_W['uniacid'],
                    ':is_del'    =>0
                ));
                $module_post['img']         = iunserializer($module_post['img']);
                $module_post['condition']   = iunserializer($module_post['condition']);

                //第一个过滤条件的信息
                $first_filter_img   = array_shift($module_post['img']);
                $first_filter_type  = array_shift($module_post['condition']['type']);
                $first_filter_val   = array_shift($module_post['condition']['val']);
                if(empty($module_post)) {
                    message('样式模块不存在或已删除', '', 'error');
                }
            } else {
                $country = array(
                    'displayorder' => 0
                );
            }
            if (checksubmit('submit')) {
                pdo_begin();
                if (empty($_GPC['name'])) {
                    message('抱歉，请输入过滤名称！');
                }
                //处理过滤条件
                $condition = ['type'=>[],'val'=>[]];
                foreach ($_GPC as $key => $value) {
                    if(strpos($key,'condition_') !== false){
                        $condition['type'][]    = $value['parentid'];
                        $condition['val'][]     = $value['childid'];
                    }
                }
                $data = array(
                    'uniacid'       => $_W['uniacid'],
                    'name'          => $_GPC['name'],
                    'displayorder'  => intval($_GPC['displayorder']),
                    'enabled'       => intval($_GPC['enabled']),
                    'show_title'    => intval($_GPC['show_title']),
                    'pid'           => intval($_GPC['pid']),
                    'condition'     => iserializer($condition),
                    'img'           => iserializer($_GPC['img']),
                );

                if (!empty($filter_id)) {
                    update('yuexiage_travelmall_module_filter', $data, array('filter_id' => $filter_id));
                    $data = array(
                        'uniacid'       => $_W['uniacid'],
                        'name'          => $_GPC['name'],
                        'displayorder'  => intval($_GPC['displayorder']),
                        'enabled'       => intval($_GPC['enabled']),
                        'pid'           => intval($_GPC['pid']),
                        'type'          => 'filter',
                        'mid'           => $filter_id
                    );
                    update('yuexiage_travelmall_module', $data, array('mid' => $filter_id,'type'=>'filter'));
                } else {

                    $data['filter_id'] = uuid();
                    insert('yuexiage_travelmall_module_filter', $data);

                    $data = array(
                        'uniacid'       => $_W['uniacid'],
                        'name'          => $_GPC['name'],
                        'displayorder'  => intval($_GPC['displayorder']),
                        'enabled'       => intval($_GPC['enabled']),
                        'pid'           => intval($_GPC['pid']),
                        'type'          => 'filter',
                        'mid'           => $data['filter_id']
                    );
                    insert('yuexiage_travelmall_module', $data);
                }
                pdo_commit();
                message('更新过滤模块成功！', $this->createWebUrl('module', array('op' => 'display','action'=>'filter')), 'success');
            }
        }elseif ($do == 'delete') {
            $filter_id = $_GPC['id'];
            if(!empty($filter_id)) {
                $module = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_module_filter')." WHERE filter_id = :filter_id AND uniacid = :uniacid and is_del = :is_del",array(
                    ':filter_id' =>$filter_id,
                    ':uniacid'   =>$_W['uniacid'],
                    ':is_del'    =>0
                ));
                if(empty($module)) {
                    message('过滤模块不存在或已删除', '', 'error');
                }
            }else{
                $module = array(
                    'displayorder' => 0
                );
            }
            pdo_begin();
            update('yuexiage_travelmall_module_filter', ['is_del'=>1], array('filter_id' => $filter_id));
            update('yuexiage_travelmall_module', ['is_del'=>1], array('mid' => $filter_id));
            pdo_commit();
            message('删除过滤模块成功！', $this->createWebUrl('module', array('op' => 'display','action'=>'filter')), 'success');
        }
    }elseif ($action=='style') {
        if ($do == 'display') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $style_id => $displayorder) {
                    $update = array('displayorder' => $displayorder);
                    update('yuexiage_travelmall_module', $update, array('mid' => $style_id));
                }
                message('样式排序更新成功！', 'refresh', 'success');
            }

            $params     = [':is_del'=>0];
            $condition  = " and is_del = :is_del";
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND name LIKE :keyword";
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }
            $params[':uniacid'] = $_W['uniacid'];
            $modules = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_module_style')." WHERE uniacid = :uniacid $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
            $total   = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_module_style') . " WHERE uniacid = :uniacid $condition ",$params);
            $pager   = pagination($total, $pindex, $psize);
        }elseif ($do == 'post') {
            $style_id = $_GPC['id'];
            if(!empty($style_id)) {
                $module_post = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_module_style')." WHERE style_id = :style_id AND uniacid = :uniacid and is_del = :is_del",array(
                    ':style_id' => $style_id,
                    ':uniacid'  => $_W['uniacid'],
                    ':is_del'   => 0
                ));
                $module_post['img'] = iunserializer($module_post['img']);
                $module_post['lnk'] = iunserializer($module_post['lnk']);
                if(empty($module_post)) {
                    message('样式模块不存在或已删除', '', 'error');
                }
            } else {
                $country = array(
                    'displayorder' => 0
                );
            }
            if (checksubmit('submit')) {
                pdo_begin();
                if (empty($_GPC['name'])) {
                    message('抱歉，请输入样式名称！');
                }
                $data = array(
                    'uniacid'       => $_W['uniacid'],
                    'name'          => $_GPC['name'],
                    'displayorder'  => intval($_GPC['displayorder']),
                    'enabled'       => intval($_GPC['enabled']),
                    'show_title'    => intval($_GPC['show_title']),
                    'pid'           => intval($_GPC['pid']),
                    'rows'          => intval($_GPC['row']),
                    'columns'       => intval($_GPC['column']),
                    'img'           => iserializer($_GPC['img']),
                    'lnk'           => iserializer($_GPC['lnk'])
                );

                if (!empty($style_id)) {
                    update('yuexiage_travelmall_module_style', $data, array('style_id' => $style_id));
                    $data = array(
                        'uniacid'       => $_W['uniacid'],
                        'name'          => $_GPC['name'],
                        'displayorder'  => intval($_GPC['displayorder']),
                        'enabled'       => intval($_GPC['enabled']),
                        'pid'           => intval($_GPC['pid']),
                        'type'          => 'style',
                        'mid'           => $style_id
                    );
                    update('yuexiage_travelmall_module', $data, array('mid' => $style_id,'type'=>'style'));
                } else {
                    $data['style_id']  = uuid();
                    insert('yuexiage_travelmall_module_style', $data);
                    $data = array(
                        'uniacid'       => $_W['uniacid'],
                        'name'          => $_GPC['name'],
                        'displayorder'  => intval($_GPC['displayorder']),
                        'enabled'       => intval($_GPC['enabled']),
                        'pid'           => intval($_GPC['pid']),
                        'type'          => 'style',
                        'mid'           => $data['style_id']
                    );
                    insert('yuexiage_travelmall_module', $data);
                }
                pdo_commit();
                message('更新样式成功！', $this->createWebUrl('module', array('op' => 'display','action'=>'style')), 'success');
            }
        }elseif ($do == 'delete') {
            $style_id = $_GPC['id'];
            if(!empty($style_id)) {
                $module = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_module_style')." WHERE style_id = :style_id AND uniacid = :uniacid and is_del = :is_del",array(
                    ':style_id' => $style_id,
                    ':uniacid'  => $_W['uniacid'],
                    ':is_del'   => 0
                ));
                if(empty($module)) {
                    message('样式模块不存在或已删除', '', 'error');
                }
            }else{
                $module = array(
                    'displayorder' => 0
                );
            }
            pdo_begin();
            update('yuexiage_travelmall_module_style', ['is_del'=>1], array('style_id' => $style_id));
            update('yuexiage_travelmall_module', ['is_del'=>1], array('mid' => $style_id));
            pdo_commit();
            message('删除样式成功！', $this->createWebUrl('module', array('op' => 'display','action'=>'style')), 'success');
        }
    }elseif($action=='category') {
        if ($do == 'display') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $category_id => $displayorder) {
                    $update = array('displayorder' => $displayorder);
                    update('yuexiage_travelmall_module', $update, array('mid' => $category_id));
                }
                message('分类排序更新成功！', 'refresh', 'success');
            }

            $params     = [':is_del'=>0];
            $condition  = " and is_del = :is_del";
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND name LIKE :keyword";
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }

            $params[':uniacid'] = $_W['uniacid'];
            $modules = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_module_category')." WHERE uniacid = :uniacid $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
            $total   = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_module_category') . " WHERE uniacid = :uniacid $condition ",$params);
            $pager   = pagination($total, $pindex, $psize);
        }elseif ($do == 'post') {
            $category_id = $_GPC['id'];
            if(!empty($category_id)) {
                $module_post = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_module_category')." WHERE category_id = :category_id AND uniacid = :uniacid and is_del = :is_del",array(
                    ':category_id' =>$category_id,
                    ':uniacid'     =>$_W['uniacid'],
                    ":is_del"      =>0,
                ));
                $module_post['img'] = iunserializer($module_post['img']);
                $img                = $module_post['img'];
                if(empty($module_post)) {
                    message('分类模块不存在或已删除', '', 'error');
                }
            } else {
                $country = array(
                    'displayorder' => 0
                );
            }

            //查询类别
            $categorys  =  com_load_cache(array(
                'cache_key'=>'category',
            ));
            if (checksubmit('submit')) {
                pdo_begin();
                if (empty($_GPC['name'])) {
                    message('抱歉，请输入分类模块名称！');
                }
                $data = array(
                    'uniacid'       => $_W['uniacid'],
                    'name'          => $_GPC['name'],
                    'displayorder'  => intval($_GPC['displayorder']),
                    'enabled'       => intval($_GPC['enabled']),
                    'show_title'    => intval($_GPC['show_title']),
                    'pid'           => intval($_GPC['pid']),
                    'img'           => iserializer($_GPC['img'])
                );
                if (!empty($category_id)) {
                    update('yuexiage_travelmall_module_category', $data, array('category_id' => $category_id));
                    $data = array(
                        'uniacid'       => $_W['uniacid'],
                        'name'          => $_GPC['name'],
                        'displayorder'  => intval($_GPC['displayorder']),
                        'enabled'       => intval($_GPC['enabled']),
                        'pid'           => intval($_GPC['pid']),
                        'type'          => 'category',
                        'mid'           => $category_id
                    );

                    update('yuexiage_travelmall_module', $data, array('mid' => $category_id,'type'=>'category'));
                } else {
                    $data['category_id'] = uuid();
                    insert('yuexiage_travelmall_module_category', $data);
                    $data = array(
                        'uniacid'       => $_W['uniacid'],
                        'name'          => $_GPC['name'],
                        'displayorder'  => intval($_GPC['displayorder']),
                        'enabled'       => intval($_GPC['enabled']),
                        'pid'           => intval($_GPC['pid']),
                        'type'          => 'category',
                        'mid'           => $data['category_id']
                    );
                    insert('yuexiage_travelmall_module', $data);
                }
                pdo_commit();
                message('更新分类模块成功！', $this->createWebUrl('module', array('op' => 'display','action'=>'category')), 'success');
            }
        }elseif ($do == 'delete') {
            $category_id = $_GPC['id'];
            if(!empty($category_id)) {
                $module = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_module_category')." WHERE category_id = :category_id AND uniacid = :uniacid and is_del = :is_del",array(
                    ':category_id' =>$category_id,
                    ':uniacid'     =>$_W['uniacid'],
                    ":is_del"      =>0,
                ));
                if(empty($module)) {
                    message('分类模块不存在或已删除', '', 'error');
                }
            }else{
                $module = array(
                    'displayorder' => 0
                );
            }
            pdo_begin();
            update('yuexiage_travelmall_module_category', ['is_del'=>1], array('category_id' => $category_id));
            update('yuexiage_travelmall_module', ['is_del'=>1], array('mid' => $category_id));
            pdo_commit();
            message('删除分类模块成功！', $this->createWebUrl('module', array('op' => 'display','action'=>'category')), 'success');
        }
    }elseif($action=='theme') {
        if ($do == 'display') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $theme_id => $displayorder) {
                    $update = array('displayorder' => $displayorder);
                    update('yuexiage_travelmall_module', $update, array('mid' => $theme_id));
                }
                message('主题模块排序更新成功！', 'refresh', 'success');
            }

            $params     = [':is_del'=>0];
            $condition  = " and is_del = :is_del";
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND name LIKE :keyword";
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }
            $params[':uniacid'] = $_W['uniacid'];
            $modules    = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_module_theme')." WHERE uniacid = :uniacid $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
            $total      = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_module_theme') . " WHERE uniacid = :uniacid $condition ",$params);
            $pager      = pagination($total, $pindex, $psize);
        }elseif ($do == 'post') {
            $theme_model_id = $_GPC['id'];
            if(!empty($theme_model_id)) {
                $module_post        = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_module_theme')." WHERE theme_model_id = :theme_model_id AND uniacid = :uniacid and is_del = :is_del",array(
                    ':is_del'   =>0,
                    ':uniacid'  =>$_W['uniacid'],
                    ':theme_model_id' =>$theme_model_id,
                ));
                $module_post['img'] = iunserializer($module_post['img']);
                $img                = $module_post['img'];
                if(empty($module_post)) {
                    message('主题模块不存在或已删除', '', 'error');
                }
            } else {
                $country = array(
                    'displayorder' => 0
                );
            }

            //查询主题
             $themes     =  com_load_cache(array(
                'cache_key'=>'theme',
             ));

            if (checksubmit('submit')) {
                pdo_begin();
                if (empty($_GPC['name'])) {
                    message('抱歉，请输入主题模块名称！');
                }
                $data = array(
                    'uniacid'       => $_W['uniacid'],
                    'name'          => $_GPC['name'],
                    'displayorder'  => intval($_GPC['displayorder']),
                    'enabled'       => intval($_GPC['enabled']),
                    'show_title'    => intval($_GPC['show_title']),
                    'pid'           => intval($_GPC['pid']),
                    'img'           => iserializer($_GPC['img']),
                    'theme_id'      => iserializer($_GPC['theme_id']),
                );

                if (!empty($theme_model_id)) {
                    update('yuexiage_travelmall_module_theme', $data, array('theme_model_id' => $theme_model_id));
                    $data = array(
                        'uniacid'       => $_W['uniacid'],
                        'name'          => $_GPC['name'],
                        'displayorder'  => intval($_GPC['displayorder']),
                        'enabled'       => intval($_GPC['enabled']),
                        'pid'           => intval($_GPC['pid']),
                        'type'          => 'theme',
                        'mid'           => $theme_model_id
                    );
                    update('yuexiage_travelmall_module', $data, array('mid' => $theme_model_id,'type'=>'theme'));
                } else {
                    $data['theme_model_id'] = uuid();
                    insert('yuexiage_travelmall_module_theme', $data);
                    $data = array(
                        'uniacid'       => $_W['uniacid'],
                        'name'          => $_GPC['name'],
                        'displayorder'  => intval($_GPC['displayorder']),
                        'enabled'       => intval($_GPC['enabled']),
                        'pid'           => intval($_GPC['pid']),
                        'type'          => 'theme',
                        'mid'           => $data['theme_model_id']
                    );
                    insert('yuexiage_travelmall_module', $data);
                }
                pdo_commit();
                message('更新主题模块成功！', $this->createWebUrl('module', array('op' => 'display','action'=>'theme')), 'success');
            }
        }elseif ($do == 'delete') {
            $theme_model_id = $_GPC['id'];
            if(!empty($theme_model_id)) {
                $module     = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_module_theme')." WHERE theme_model_id = :theme_model_id AND uniacid = :uniacid and is_del = :is_del",array(
                    ':is_del'       =>0,
                    ':uniacid'      =>$_W['uniacid'],
                    ':theme_model_id' =>$theme_model_id,
                ));
                if(empty($module)) {
                    message('主题模块不存在或已删除', '', 'error');
                }
            }else{
                $module = array(
                    'displayorder' => 0
                );
            }
            pdo_begin();
            update('yuexiage_travelmall_module_theme', ['is_del'=>1], array('theme_model_id' => $theme_model_id));
            update('yuexiage_travelmall_module', ['is_del'=>1], array('mid' => $theme_model_id));
            pdo_commit();
            message('删除主题模块成功！', $this->createWebUrl('module', array('op' => 'display','action'=>'theme')), 'success');
        }
    }elseif($action=='tab') {
        if ($do == 'display') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $tab_id => $displayorder) {
                    $update = array('displayorder' => $displayorder);
                    update('yuexiage_travelmall_module', $update, array('mid' => $tab_id));
                }
                message('标签模块排序更新成功！', 'refresh', 'success');
            }

            $params     = [':is_del'=>0];
            $condition  = " and is_del = :is_del";
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND name LIKE :keyword";
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }
            $params[':uniacid'] = $_W['uniacid'];
            $modules = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_module_tab')." WHERE uniacid = :uniacid $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
            $total   = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_module_tab') . " WHERE uniacid = :uniacid $condition ",$params);
            $pager   = pagination($total, $pindex, $psize);
        }elseif ($do == 'post') {
            $tab_id  = $_GPC['id'];
            if(!empty($tab_id)) {
                $module_post = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_module_tab')." WHERE tab_id = :tab_id AND uniacid = :uniacid and is_del = :is_del",array(
                    ':is_del'   =>0,
                    ':uniacid'  =>$_W['uniacid'],
                    ':tab_id'   =>$tab_id,
                ));
                $module_post['img'] = iunserializer($module_post['img']);
                $img                = $module_post['img'];
                if(empty($module_post)) {
                    message('标签模块不存在或已删除', '', 'error');
                }
            } else {
                $country = array(
                    'displayorder' => 0
                );
            }

            //查询标签
            $tabs =  com_load_cache(array(
                'cache_key'=>'tab',
            ));

            if (checksubmit('submit')) {
                pdo_begin();
                if (empty($_GPC['name'])) {
                    message('抱歉，请输入标签模块名称！');
                }
                $data = array(
                    'uniacid'       => $_W['uniacid'],
                    'name'          => $_GPC['name'],
                    'displayorder'  => intval($_GPC['displayorder']),
                    'enabled'       => intval($_GPC['enabled']),
                    'show_title'    => intval($_GPC['show_title']),
                    'pid'           => intval($_GPC['pid']),
                    'img'           => iserializer($_GPC['img'])
                );

                if (!empty($tab_id)) {
                    update('yuexiage_travelmall_module_tab', $data, array('tab_id' => $tab_id));
                    $data = array(
                        'uniacid'       => $_W['uniacid'],
                        'name'          => $_GPC['name'],
                        'displayorder'  => intval($_GPC['displayorder']),
                        'enabled'       => intval($_GPC['enabled']),
                        'pid'           => intval($_GPC['pid']),
                        'type'          => 'tab',
                        'mid'           => $tab_id
                    );
                    update('yuexiage_travelmall_module', $data, array('mid' => $tab_id,'type'=>'tab'));
                } else {
                    $data['tab_id']     = uuid();
                    insert('yuexiage_travelmall_module_tab', $data);
                    $data = array(
                        'uniacid'       => $_W['uniacid'],
                        'name'          => $_GPC['name'],
                        'displayorder'  => intval($_GPC['displayorder']),
                        'enabled'       => intval($_GPC['enabled']),
                        'pid'           => intval($_GPC['pid']),
                        'type'          => 'tab',
                        'mid'           => $data['tab_id']
                    );
                    insert('yuexiage_travelmall_module', $data);
                }
                pdo_commit();
                message('更新标签模块成功！', $this->createWebUrl('module', array('op' => 'display','action'=>'tab')), 'success');
            }
        }elseif ($do == 'delete') {
            $tab_id = $_GPC['id'];
            if(!empty($tab_id)) {

                $module = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_module_tab')." WHERE tab_id = :tab_id AND uniacid = :uniacid and is_del = :is_del",array(
                    ':is_del'   =>0,
                    ':uniacid'  =>$_W['uniacid'],
                    ':tab_id'   =>$tab_id,
                ));
                if(empty($module)) {
                    message('标签模块不存在或已删除', '', 'error');
                }
            }else{
                $module = array(
                    'displayorder' => 0
                );
            }
            pdo_begin();
            update('yuexiage_travelmall_module_tab', ['is_del'=>1], array('tab_id' => $tab_id));
            update('yuexiage_travelmall_module', ['is_del'=>1], array('mid' => $tab_id));
            pdo_commit();
            message('删除标签模块成功！', $this->createWebUrl('module', array('op' => 'display','action'=>'tab')), 'success');
        }
    }elseif($action=='spike') {
        if ($do == 'display') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $id => $displayorder) {
                    $update = array('displayorder' => $displayorder);
                    update('yuexiage_travelmall_module', $update, array('mid' => $spike_id));
                }
                message('秒杀模块排序更新成功！', 'refresh', 'success');
            }
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND name LIKE :keyword";
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }
            $modules = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_module_spike')." WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);

            $total=pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_module_spike') . " WHERE uniacid = '{$_W['uniacid']}' $condition ",$params);
            $pager = pagination($total, $pindex, $psize);
        }elseif ($do == 'post') {
            $id = intval($_GPC['id']);
            if(!empty($id)) {
                $module_post = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_module_spike')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
                $module_post['time']  = iunserializer($module_post['time']);
                $module_post['line']  = iunserializer($module_post['line']);
                $time                 = $module_post['time'];
                $line                 = $module_post['line'];
                if(empty($module_post)) {
                    message('秒杀模块不存在或已删除', '', 'error');
                }
            } else {
                $country = array(
                    'displayorder' => 0
                );
            }

            if (checksubmit('submit')) {
                if (empty($_GPC['name'])) {
                    message('抱歉，请输入秒杀模块名称！');
                }
                $data = array(
                    'uniacid'       => $_W['uniacid'],
                    'name'          => $_GPC['name'],
                    'displayorder'  => intval($_GPC['displayorder']),
                    'enabled'       => intval($_GPC['enabled']),
                    'pid'           => intval($_GPC['pid']),
                    'time'          => iserializer($_GPC['time']),
                    'line'          => iserializer($_GPC['line'])
                );

                if (!empty($id)) {
                    update('yuexiage_travelmall_module_spike', $data, array('id' => $id));
                    $data = array(
                        'uniacid'       => $_W['uniacid'],
                        'name'          => $_GPC['name'],
                        'displayorder'  => intval($_GPC['displayorder']),
                        'enabled'       => intval($_GPC['enabled']),
                        'pid'           => intval($_GPC['pid']),
                        'type'          => 'spike',
                        'mid'           => $id
                    );
                    update('yuexiage_travelmall_module', $data, array('mid' => $id,'type'=>'spike'));
                } else {
                    insert('yuexiage_travelmall_module_spike', $data);
                    $id = insertid();
                    $data = array(
                        'uniacid'       => $_W['uniacid'],
                        'name'          => $_GPC['name'],
                        'displayorder'  => intval($_GPC['displayorder']),
                        'enabled'       => intval($_GPC['enabled']),
                        'pid'           => intval($_GPC['pid']),
                        'type'          => 'spike',
                        'mid'           => $id
                    );
                    insert('yuexiage_travelmall_module', $data);
                }
                message('更新秒杀模块成功！', $this->createWebUrl('module', array('op' => 'display','action'=>'spike')), 'success');
            }
        }elseif ($do == 'delete') {
            $id = intval($_GPC['id']);
            if(!empty($id)) {
                $module = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_module_spike')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
                if(empty($module)) {
                    message('秒杀模块不存在或已删除', '', 'error');
                }
            }else{
                $module = array(
                    'displayorder' => 0
                );
            }
            pdo_delete('yuexiage_travelmall_module_spike',  array('id' => $id));
            pdo_delete('yuexiage_travelmall_module',  array('mid' => $id));
            message('删除秒杀模块成功！', $this->createWebUrl('module', array('op' => 'display','action'=>'spike')), 'success');
        }
    }elseif($action=='poop') {
        if ($do == 'display') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $id => $displayorder) {
                    $update = array('displayorder' => $displayorder);
                    update('yuexiage_travelmall_module', $update, array('mid' => $poop_id));
                }
                message('甩尾模块排序更新成功！', 'refresh', 'success');
            }
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND name LIKE :keyword";
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }
            $modules = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_module_poop')." WHERE uniacid = '{$_W['uniacid']}' $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);

            $total=pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_module_poop') . " WHERE uniacid = '{$_W['uniacid']}' $condition ",$params);
            $pager = pagination($total, $pindex, $psize);
        }elseif ($do == 'post') {
            $id = intval($_GPC['id']);
            if(!empty($id)) {
                $module_post = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_module_poop')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
                $module_post['line']  = iunserializer($module_post['line']);
                $line                 = $module_post['line'];
                if(empty($module_post)) {
                    message('甩尾模块不存在或已删除', '', 'error');
                }
            } else {
                $country = array(
                    'displayorder' => 0
                );
            }

            if (checksubmit('submit')) {
                if (empty($_GPC['name'])) {
                    message('抱歉，请输入甩尾模块名称！');
                }
                $data = array(
                    'uniacid'       => $_W['uniacid'],
                    'name'          => $_GPC['name'],
                    'displayorder'  => intval($_GPC['displayorder']),
                    'enabled'       => intval($_GPC['enabled']),
                    'show_title'    => intval($_GPC['show_title']),
                    'pid'           => intval($_GPC['pid']),
                    'line'          => iserializer($_GPC['line'])
                );

                if (!empty($id)) {
                    update('yuexiage_travelmall_module_poop', $data, array('id' => $id));
                    $data = array(
                        'uniacid'       => $_W['uniacid'],
                        'name'          => $_GPC['name'],
                        'displayorder'  => intval($_GPC['displayorder']),
                        'enabled'       => intval($_GPC['enabled']),
                        'pid'           => intval($_GPC['pid']),
                        'type'          => 'poop',
                        'mid'           => $id
                    );
                    update('yuexiage_travelmall_module', $data, array('mid' => $id,'type'=>'poop'));
                } else {
                    insert('yuexiage_travelmall_module_poop', $data);
                    $id = insertid();
                    $data = array(
                        'uniacid'       => $_W['uniacid'],
                        'name'          => $_GPC['name'],
                        'displayorder'  => intval($_GPC['displayorder']),
                        'enabled'       => intval($_GPC['enabled']),
                        'pid'           => intval($_GPC['pid']),
                        'type'          => 'poop',
                        'mid'           => $id
                    );
                    insert('yuexiage_travelmall_module', $data);
                }
                message('更新甩尾模块成功！', $this->createWebUrl('module', array('op' => 'display','action'=>'poop')), 'success');
            }
        }elseif ($do == 'delete') {
            $id = intval($_GPC['id']);
            if(!empty($id)) {
                $module = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_module_poop')." WHERE id = '$id' AND uniacid = {$_W['uniacid']}");
                if(empty($module)) {
                    message('甩尾模块不存在或已删除', '', 'error');
                }
            }else{
                $module = array(
                    'displayorder' => 0
                );
            }
            pdo_delete('yuexiage_travelmall_module_poop',  array('id' => $id));
            pdo_delete('yuexiage_travelmall_module',  array('mid' => $id));
            message('删除甩尾模块成功！', $this->createWebUrl('module', array('op' => 'display','action'=>'poop')), 'success');
        }
    }elseif($action=='recommend') {
        if ($do == 'display') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $recommend_id => $displayorder) {
                    $update = array('displayorder' => $displayorder);
                    update('yuexiage_travelmall_module', $update, array('mid' => $recommend_id));
                }
                message('推荐模块排序更新成功！', 'refresh', 'success');
            }

            $params         = [':is_del'=>0];
            $condition      = " and is_del = :is_del";
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND name LIKE :keyword";
                $params[':keyword'] = "%{$_GPC['keyword']}%";
            }

            $params[':uniacid'] = $_W['uniacid'];
            $modules    = pdo_fetchall("SELECT * FROM ".tablename('yuexiage_travelmall_module_recommend')." WHERE uniacid = :uniacid $condition ORDER BY  displayorder ASC, id LIMIT ".($pindex - 1) * $psize.','.$psize,$params);
            $total      = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('yuexiage_travelmall_module_recommend') . " WHERE uniacid = :uniacid $condition ",$params);
            $pager      = pagination($total, $pindex, $psize);
        }elseif ($do == 'post') {
            $recommend_id = $_GPC['id'];
            if(!empty($recommend_id)) {
                $module_post = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_module_recommend')." WHERE recommend_id = :recommend_id AND uniacid = :uniacid",array(
                    ":recommend_id" => $recommend_id,
                    ':uniacid'      => $_W['uniacid']
                ));
                $module_post['img']  = iunserializer($module_post['img']);
                $imgs                = $module_post['img'];
                if(empty($module_post)) {
                    message('推荐模块不存在或已删除', '', 'error');
                }
            } else {
                $country = array(
                    'displayorder' => 0
                );
            }
            //获取模板列表
            load()->func('file');
            $files = scandir(MODULE_ROOT . '/template/mobile/module/recommend/');
            unset($files[0],$files[1]);

            if (checksubmit('submit')) {
                pdo_begin();
                if (empty($_GPC['name'])) {
                    message('抱歉，请输入推荐模块名称！');
                }
                $data = array(
                    'uniacid'       => $_W['uniacid'],
                    'name'          => $_GPC['name'],
                    'style'         => $_GPC['style'],
                    'displayorder'  => intval($_GPC['displayorder']),
                    'enabled'       => intval($_GPC['enabled']),
                    'hidden_padding'=> intval($_GPC['hidden_padding']),
                    'show_title'    => intval($_GPC['show_title']),
                    'pid'           => intval($_GPC['pid']),
                    'img'           => iserializer($_GPC['recommend'])
                );

                if (!empty($recommend_id)) {
                    update('yuexiage_travelmall_module_recommend', $data, array('recommend_id' => $recommend_id));
                    $data = array(
                        'uniacid'       => $_W['uniacid'],
                        'name'          => $_GPC['name'],
                        'displayorder'  => intval($_GPC['displayorder']),
                        'enabled'       => intval($_GPC['enabled']),
                        'pid'           => intval($_GPC['pid']),
                        'type'          => 'recommend',
                        'mid'           => $recommend_id
                    );
                    update('yuexiage_travelmall_module', $data, array('mid' => $recommend_id,'type'=>'recommend'));
                } else {
                    $data['recommend_id'] = uuid();
                    insert('yuexiage_travelmall_module_recommend', $data);
                    $data = array(
                        'uniacid'       => $_W['uniacid'],
                        'name'          => $_GPC['name'],
                        'displayorder'  => intval($_GPC['displayorder']),
                        'enabled'       => intval($_GPC['enabled']),
                        'pid'           => intval($_GPC['pid']),
                        'type'          => 'recommend',
                        'mid'           => $data['recommend_id']
                    );
                    insert('yuexiage_travelmall_module', $data);
                }
                pdo_commit();
                message('更新推荐模块成功！', $this->createWebUrl('module', array('op' => 'display','action'=>'recommend')), 'success');
            }
        }elseif ($do == 'delete') {
            $recommend_id = $_GPC['id'];
            if(!empty($recommend_id)) {
                $module = pdo_fetch("SELECT * FROM ".tablename('yuexiage_travelmall_module_recommend')." WHERE recommend_id = :recommend_id AND uniacid = :uniacid",array(
                    ":recommend_id" => $recommend_id,
                    ':uniacid'      => $_W['uniacid']
                ));
                if(empty($module)) {
                    message('推荐模块不存在或已删除', '', 'error');
                }
            }else{
                $module = array(
                    'displayorder' => 0
                );
            }
            pdo_begin();
            update('yuexiage_travelmall_module_recommend', ['is_del'=>1], array('recommend_id' => $recommend_id));
            update('yuexiage_travelmall_module', ['is_del'=>1], array('mid' => $recommend_id));
            pdo_commit();
            message('删除推荐模块成功！', $this->createWebUrl('module', array('op' => 'display','action'=>'recommend')), 'success');
        }elseif ($do == 'move_item') {
            include $this->template('template/recommendlist');
            exit;
        }
    }
    include $this->template('module/'.$action);
} catch (Exception $e) {
    pdo_rollback();
    echo $e->getMessage();
}