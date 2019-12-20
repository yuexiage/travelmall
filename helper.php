<?php
/**
 * Created by PhpStorm.
 * User: 墩儿
 * Date: 2019/11/21
 * Time: 21:13
 */
//定义缓存名称分隔符
define('SEP',':');
define('DS',DIRECTORY_SEPARATOR);
define('TABLEPRE',$_W['config']['db']['tablepre']);
include_once 'lib/config_cache.php';
/**
 * @param $data
 * @param string $code
 * @return mixed|string
 */
function json($data,$code = '20',$msg=''){
    return json_encode(['data'=>$data,'code'=>$code,'msg'=>$msg]);
}

/**
 * @param $key 缓存名称
 */
function com_load_cache($cache_data){
    global $_W;
    //基本配置校验
    if (empty($cache_data['cache_key'])) {
        //构建缓存传入的参数
        throw new Exception("缓存调用失败！!",'42');
    }
    //获取缓存构建配置
    $cache_key = $cache_data['cache_key'];
    foreach ($_W['cache_clear'][$_W['cache_create'][$cache_key]['table']][$cache_data['cache_key']] as $key => $value) {
        if (empty($value)) {
            continue;
        }
        if (!isset($cache_data[$value])) {
            throw new Exception($value, '缺少缓存' . $cache_key . '构建元素！','42');
        }
        $cache_key .= SEP . $cache_data[$value];
    }

    cache_delete($cache_key);
    //读取缓存信息
    $result = cache_load($cache_key);
    //如果缓存不存在，调用缓存生成方法
    if( empty($result) ){
        $arr = explode(SEP,$cache_key);
        return com_makc_cache(array_shift($arr),$arr);
    }
    return $result;
}

/**
 * @param $cache_file
 * @param $params
 * 生成缓存
 */
function com_makc_cache($cache_file,$params){
    include_once 'lib'.DS.'lib_cache_'.$cache_file.'.php';
    $org = new $cache_file();
    return $org->make_chache($params);
}

/**
 * 生成uuid
 * @return [type] [description]
 */
function uuid() {     
    static $guid = '';
    $uid = uniqid("", true);
    $data = '';
    $data .= $_SERVER['REQUEST_TIME'];
    $data .= $_SERVER['HTTP_USER_AGENT'];
    $data .= $_SERVER['LOCAL_ADDR'];
    $data .= $_SERVER['LOCAL_PORT'];
    $data .= $_SERVER['REMOTE_ADDR'];
    $data .= $_SERVER['REMOTE_PORT'];
    $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
    $guid = substr($hash,  0,  8) . 
            '-' .
            substr($hash,  8,  4) .
            '-' .
            substr($hash, 12,  4) .
            '-' .
            substr($hash, 16,  4) .
            '-' .
            substr($hash, 20, 12) 
            ;
    return md5($guid);
  }

/***
 * 查询国家和城市联动信息-不包含已下架
 */
function select_country_city(){
    //查询国家信息
    $countrys =  com_load_cache(array(
        'cache_key'=>'country',
    ));
    //查询城市信息
    $citys =  com_load_cache(array(
        'cache_key'=>'city',
    ));
    foreach ($citys as $key => $city) {
        $_citys[$city['pcate']][] = $city;
    }
    return ['countrys'=>$countrys,'_citys'=>$_citys,'citys'=>$citys];
}

/***
 * 查询国家和城市联动信息-包含已下架
 */
function select_country_city_all(){
    //查询国家信息
    $countrys =  com_load_cache(array(
        'cache_key'=>'country_all',
    ));

    //查询城市信息
    $citys =  com_load_cache(array(
        'cache_key'=>'city_all',
    ));
    foreach ($citys as $key => $city) {
        $_citys[$city['pcate']][] = $city;
    }
    return ['countrys'=>$countrys,'_citys'=>$_citys,'citys'=>$citys];
}

/**
 * @param $table
 * @param array $data
 * @param array $params
 * @param string $glue
 * @return bool
 * @throws Exception
 * 封装更新方法
 */
function update($table, $data = array(), $params = array(), $glue = 'AND') {
    global $_W;
    $re = pdo()->update($table, $data, $params, $glue);
    if (!empty($re)){
        $table = TABLEPRE.$table;
        if(!empty($_W['cache_clear'][$table])){
            //缓存key前缀
            $authkey = $GLOBALS['_W']['config']['setting']['authkey'];
            foreach ($_W['cache_clear'][$table] as $key=>$cache){
                //删除$key开头的所有缓存
                foreach (cache_search($key) as $k=>$val){
                    $_cache_key = str_replace($authkey, "", $k);
                    cache_delete($_cache_key);
                    logging_run('删除缓存：'.$_cache_key, 'cache', 'cache');
                }
                //删除结束
            }
        }
    }
    return $re;
}

/**
 * @param $table
 * @param array $data
 * @param bool $replace
 * @return bool
 * @throws Exception
 * 封装添加方法
 */
function insert($table, $data = array(), $replace = FALSE) {
    global $_W;
    $re = pdo()->insert($table, $data, $replace);
    if (!empty($re)){
        $table = TABLEPRE.$table;
        if(!empty($_W['cache_clear'][$table])){
            //缓存key前缀
            $authkey = $GLOBALS['_W']['config']['setting']['authkey'];
            foreach ($_W['cache_clear'][$table] as $key=>$cache){
                //删除$key开头的所有缓存
                foreach (cache_search($key) as $k=>$val){
                    $_cache_key = str_replace($authkey, "", $k);
                    cache_delete($_cache_key);
                    logging_run('删除缓存：'.$_cache_key, 'cache', 'cache');
                }
                //删除结束
            }
        }
    }
    return $re;
}

/**
 * @param $table
 * @param array $params
 * @param string $glue
 * @throws Exception
 * 封装删除方法
 */
function delete($table, $params = array(), $glue = 'AND') {
    global $_W;
    $re = pdo()->delete($table, $params, $glue);
    if (!empty($re)){
        $table = TABLEPRE.$table;
        if(!empty($_W['cache_clear'][$table])){
            //缓存key前缀
            $authkey = $GLOBALS['_W']['config']['setting']['authkey'];
            foreach ($_W['cache_clear'][$table] as $key=>$cache){
                //删除$key开头的所有缓存
                foreach (cache_search($key) as $k=>$val){
                    $_cache_key = str_replace($authkey, "", $k);
                    cache_delete($_cache_key);
                    logging_run('删除缓存：'.$_cache_key, 'cache', 'cache');
                }
                //删除结束
            }
        }
    }
}