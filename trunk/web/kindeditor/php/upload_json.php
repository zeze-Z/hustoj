<?php
/**
 * KindEditor PHP
 *
 * 本PHP程序是演示程序，建议不要直接在实际项目中使用。
 * 如果您确定直接使用本程序，使用之前请仔细确认相关安全设置。
 *
 */
require_once("../../include/db_info.inc.php");
if (!(isset($_SESSION[$OJ_NAME.'_'.'administrator'])
      ||isset($_SESSION[$OJ_NAME.'_'.'problem_editor'])
      ||isset($_SESSION[$OJ_NAME.'_'.'contest_creator'])
     )){
        echo "<a href='../loginpage.php'>Please Login First!</a>";
	//echo $_SESSION[$OJ_NAME.'_'.'administrator']."[$OJ_NAME]";
        exit(1);
}


$php_path = dirname(__FILE__) . '/';

/**
 * 压缩编辑器上传图片：统一压为最长边 ≤1600px、体积 ≤50KB 的 JPEG（公告等富文本展示用）
 * 压缩策略：先逐级降质量（85→73→…→25），仍超 50KB 再等比缩小尺寸（×0.85/轮）重压
 * png/bmp 压为 JPEG 后保存为 .jpg 并删除原文件（返回新路径）；jpg/jpeg 原名覆盖
 * gif（可能含动画）跳过；GD 未启用、解码失败或压缩结果反而更大时保留原图返回 false
 * @param string $file_path 已保存到盘的图片绝对路径
 * @return string|false 压缩成功返回实际保存路径（可能是改名后的 .jpg），失败返回 false
 */
function compress_uploaded_image($file_path){
	if (!function_exists('imagecreatefromstring')) return false;
	$bin = @file_get_contents($file_path);
	if ($bin === false || $bin === '') return false;
	$src = @imagecreatefromstring($bin);
	if (!$src) return false;
	$src_w = imagesx($src);
	$src_h = imagesy($src);

	// EXIF 方向修正（仅 jpeg 有该标记）：压缩重编码会丢弃 Orientation，必须先摆正，否则展示横竖颠倒
	$ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
	if (in_array($ext, array('jpg','jpeg')) && function_exists('exif_read_data')) {
		$exif = @exif_read_data($file_path);
		$orientation = (is_array($exif) && isset($exif['Orientation'])) ? intval($exif['Orientation']) : 1;
		$rotate_angle = 0; // imagerotate 为逆时针角度
		$flip_mode = '';
		switch ($orientation) {
			case 2: $flip_mode = 'h'; break;          // 水平镜像
			case 3: $rotate_angle = 180; break;       // 旋转180
			case 4: $flip_mode = 'v'; break;          // 垂直镜像
			case 5: $rotate_angle = -90; $flip_mode = 'h'; break; // 转置
			case 6: $rotate_angle = -90; break;       // 需顺时针90
			case 7: $rotate_angle = -90; $flip_mode = 'v'; break; // 反转置
			case 8: $rotate_angle = 90; break;        // 需逆时针90
		}
		if ($rotate_angle != 0 && function_exists('imagerotate')) {
			$rotated = imagerotate($src, $rotate_angle, 0);
			if ($rotated) {
				imagedestroy($src);
				$src = $rotated;
				$src_w = imagesx($src);
				$src_h = imagesy($src);
			}
		}
		if ($flip_mode != '' && function_exists('imageflip')) {
			imageflip($src, $flip_mode == 'h' ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL);
		}
	}

	// 等比缩到最长边 ≤1600，不足不放大
	$max_side = 1600;
	$long_side = max($src_w, $src_h);
	if ($long_side > $max_side) {
		$scale = $max_side / $long_side;
		$dst_w = max(1, intval(round($src_w * $scale)));
		$dst_h = max(1, intval(round($src_h * $scale)));
	} else {
		$dst_w = $src_w;
		$dst_h = $src_h;
	}

	// 白底 truecolor 画布（PNG 透明底平铺白色后统一压 JPEG）
	$dst = imagecreatetruecolor($dst_w, $dst_h);
	if (!$dst) {
		imagedestroy($src);
		return false;
	}
	$white = imagecolorallocate($dst, 255, 255, 255);
	imagefill($dst, 0, 0, $white);
	imagecopyresampled($dst, $src, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
	imagedestroy($src);

	// 目标体积 ≤50KB：质量降到下限仍超限时等比缩小尺寸（×0.85/轮）重压，最多 12 轮
	$target_bytes = 50 * 1024;
	$scale = 1.0;       // 相对首次缩放后画布的进一步缩小比例
	$best_data = false; // 兜底：轮次耗尽仍未达标时写最小的一次编码结果
	for ($round = 0; $round < 12; $round++) {
		$canvas = $dst;
		$own_canvas = false;
		if ($scale < 1.0) {
			$cw = max(1, intval(round($dst_w * $scale)));
			$ch = max(1, intval(round($dst_h * $scale)));
			$canvas = imagecreatetruecolor($cw, $ch);
			if (!$canvas) break;
			$white2 = imagecolorallocate($canvas, 255, 255, 255);
			imagefill($canvas, 0, 0, $white2);
			imagecopyresampled($canvas, $dst, 0, 0, 0, 0, $cw, $ch, $dst_w, $dst_h);
			$own_canvas = true;
		}
		$encoded = false;
		$data = false;
		for ($quality = 85; $quality >= 25; $quality -= 12) {
			ob_start();
			$encoded = imagejpeg($canvas, null, $quality);
			$data = ob_get_clean();
			if ($encoded && $data !== false && strlen($data) <= $target_bytes) break;
		}
		if ($own_canvas) imagedestroy($canvas);
		if (!$encoded || $data === false) break;
		if (strlen($data) <= $target_bytes) {
			$best_data = $data;
			break;
		}
		if ($best_data === false || strlen($data) < strlen($best_data)) $best_data = $data;
		$scale *= 0.85;
	}
	imagedestroy($dst);
	if ($best_data === false) return false;
	// 压缩结果反而比原图大（如原本就很小的图）：保留原图不动
	$orig_size = @filesize($file_path);
	if ($orig_size !== false && strlen($best_data) >= $orig_size) return false;

	$info = pathinfo($file_path);
	$dest = $info['dirname'] . '/' . $info['filename'] . '.jpg';
	// 目标名已被占用时追加短随机后缀，避免覆盖无关文件（长缓存下同名覆盖会导致旧 URL 展示错图）
	if ($dest !== $file_path && file_exists($dest)) {
		$dest = $info['dirname'] . '/' . $info['filename'] . '_' . substr(md5(uniqid(mt_rand(), true)), 0, 6) . '.jpg';
	}
	if (@file_put_contents($dest, $best_data) === false) return false;
	@chmod($dest, 0644);
	if ($dest !== $file_path) @unlink($file_path); // png/bmp 转 jpg 后删除原文件
	return $dest;
}

//文件保存目录路径
function upload_one_file($file_name,$tmp_name,$file_size){
	global $domain;
	$save_path = $php_path . '../../upload/';
	//文件保存目录URL
	$save_url = dirname(dirname(dirname($_SERVER['PHP_SELF']) )) . '/upload/';
	//定义允许上传的文件扩展名
	$ext_arr = array(
		'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
		'flash' => array('swf', 'flv'),
		'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb', "mp4"),
		'file' => array('pdf','doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
	);
	//最大文件大小
	$max_size = 400*1024*1024;

	$save_path = realpath($save_path) . '/';

	if (!$file_name) {
		alert("请选择文件。");
	}
	$i=1;
	//检查目录
	if (@is_dir($save_path) === false) {
		alert("上传目录不存在。");
	}
	//检查目录写权限
	if (@is_writable($save_path) === false) {
		alert("上传目录没有写权限。在服务器上执行下述命令解决该问题:\n chown www-data -R \"$save_path\" \n");
	}
	//检查是否已上传
	if (@is_uploaded_file($tmp_name) === false) {
		alert("上传失败。");
	}
	//检查文件大小
	if ($file_size > $max_size) {
		alert("上传文件大小超过限制。");
	}
	//获得文件扩展名
	$temp_arr = explode(".", $file_name);
	$file_ext = array_pop($temp_arr);
	$file_ext = trim($file_ext);
	$file_ext = strtolower($file_ext);
	//检查目录名
	$dir_name="";
	foreach($ext_arr as $key => $value){
	   if(in_array($file_ext,$value)){
			$dir_name=$key;
			break;
	   }
	}
	if (empty($ext_arr[$dir_name])) {
		alert("目录名不正确。".$ext_arr[$dir_name]."dirname[".($dir_name)."]");
	}
	//检查扩展名
	if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
		alert("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。");
	}
	if(strlen($domain)>0){
		$dir_name="$domain/$dir_name";
	}
	//创建文件夹
	if ($dir_name !== '') {
		$ymd = date("Ymd");
		$save_path = dirname(dirname(dirname(__FILE__)))."/upload/".$dir_name . "/$ymd/";
		$save_url = "/upload/".$dir_name . "/$ymd/";
		if (!file_exists($save_path)) {
			mkdir($save_path,0744,true);
		}
	}
	if (!file_exists($save_path)) {
		mkdir($save_path);
	}
	//新文件名
	//$new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
	$new_file_name = basename($file_name,".$file_ext") . '.' . $file_ext;
	// 同名文件已存在时追加短随机后缀而非覆盖：上传目录开启长缓存后，覆盖旧文件
	// 会导致引用旧 URL 的历史公告在缓存期内展示错误图片
	if (file_exists($save_path . $new_file_name)) {
		$new_file_name = basename($file_name,".$file_ext") . '_' . substr(md5(uniqid(mt_rand(), true)), 0, 6) . '.' . $file_ext;
	}
	//移动文件
	$file_path = $save_path . $new_file_name;
	$file_url = $save_url . $new_file_name;
	if (move_uploaded_file($tmp_name, $file_path) === false) {
		alert("上传文件失败。");
	}
	@chmod($file_path, 0644);
	// 图片压缩：jpg/jpeg/png/bmp 统一压为 ≤50KB 的 JPEG（gif 动图跳过），压缩后 URL 可能变为 .jpg
	if (in_array($file_ext, array('jpg','jpeg','png','bmp'))) {
		$compressed_path = compress_uploaded_image($file_path);
		if ($compressed_path !== false) {
			$file_path = $compressed_path;
			$file_url = $save_url . basename($compressed_path);
		}
	}
	$result=array('error' => 0, 'url' => $file_url,'save'=>basename($file_path));
	return $result;

}
//PHP上传失败
if (!empty($_FILES['imgFile']['error'][0])) {
	switch($_FILES['imgFile']['error'][0]){
		case '1':
			$error = '超过php.ini允许的大小。';
			break;
		case '2':
			$error = '超过表单允许的大小。';
			break;
		case '3':
			$error = '图片只有部分被上传。';
			break;
		case '4':
			$error = '请选择图片。';
			break;
		case '6':
			$error = '找不到临时目录。';
			break;
		case '7':
			$error = '写文件到硬盘出错。';
			break;
		case '8':
			$error = 'File upload stopped by extension。';
			break;
		case '999':
		default:
			$error = '未知错误。';
	}
	alert("123".$error.$_FILES['imgFile']['error'][0]);
}

//有上传文件时
if (empty($_FILES) === false) {
   if(is_array( $_FILES['imgFile']['tmp_name'] ) ){
	   $response = []; 
	    foreach ($_FILES['imgFile']['tmp_name'] as $key => $tmpName) {
		//原文件名
		$file_name = $_FILES['imgFile']['name'][$key];
		//文件大小
		$file_size = $_FILES['imgFile']['size'][$key];
		//检查文件名
		$result=upload_one_file($file_name,$tmpName,$file_size);
		$response[] = $result;
	    }
		header('Content-type: text/html; charset=UTF-8');
		echo json_encode($response);
		exit;
   }else{
	$result=upload_one_file($_FILES['imgFile']['name'], $_FILES['imgFile']['tmp_name'], $_FILES['imgFile']['size']);
	header('Content-type: text/html; charset=UTF-8');
	echo json_encode($result);
	exit;
   
   }
}

function alert($msg) {
	header('Content-type: text/html; charset=UTF-8');
	json_encode(array('error' => 1, 'message' => $msg));
	exit;
}

