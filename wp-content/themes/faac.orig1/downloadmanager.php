<?php
/*
Template name: Download Manager
*
*
* @package faac
*/

// hide notices
@ini_set('error_reporting', E_ALL & ~ E_NOTICE);

//- turn off compression on the server
@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 'Off');

/* 
1) eseguo la funzione openssl_decrypt sul parametro f ricevuto in get
2) ha ricevuto dei parametri validi?
   - c'è un pid? (se non c'è un PID,  ritorna 404)
   - facendo il decrypt, c'è un valore valido?  (se no, ritorna 404)  (è valido se la stringa decodificata ha come ultimi 2 caratteri il pipe + 0 o 1
3) ottengo dalla funzione decrypt i parametri URL e lock:  a questo punto se (l'utente non è loggato AND lock=1) ---> redirect sulla pagina di login (è possibile?)
4) // salva dati in tabella (vedi sotto)
5) return il file col mime type corretto
   (vedi esempio: https://stackoverflow.com/questions/7263923/how-to-force-file-download-with-php )

   *4) scrive in una tabella le seguenti informazioni:
   - timestamp
   - URL del file
   - lingua (dominio sito)
   - product_id 
   - user_id (user_id wordpress)
 
table prefix dal wp_config:
pr_ (produzione)
st_ (staging

Nome della tabella in cui salvare le statistiche:
pr_download_stats
st_download_stats */

// NOTA: QUI SIAMO SU WORDPRESS

function mime_type($filename) {

    $mime_types = array(
       'txt' => 'text/plain',
       'htm' => 'text/html',
       'html' => 'text/html',
       'css' => 'text/css',
       'json' => array('application/json', 'text/json'),
       'xml' => 'application/xml',
       'swf' => 'application/x-shockwave-flash',
       'flv' => 'video/x-flv',
  
       'hqx' => 'application/mac-binhex40',
       'cpt' => 'application/mac-compactpro',
       'csv' => array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel'),
       'bin' => 'application/macbinary',
       'dms' => 'application/octet-stream',
       'lha' => 'application/octet-stream',
       'lzh' => 'application/octet-stream',
       'exe' => array('application/octet-stream', 'application/x-msdownload'),
       'class' => 'application/octet-stream',
       'so' => 'application/octet-stream',
       'sea' => 'application/octet-stream',
       'dll' => 'application/octet-stream',
       'oda' => 'application/oda',
       'ps' => 'application/postscript',
       'smi' => 'application/smil',
       'smil' => 'application/smil',
       'mif' => 'application/vnd.mif',
       'wbxml' => 'application/wbxml',
       'wmlc' => 'application/wmlc',
       'dcr' => 'application/x-director',
       'dir' => 'application/x-director',
       'dxr' => 'application/x-director',
       'dvi' => 'application/x-dvi',
       'gtar' => 'application/x-gtar',
       'gz' => 'application/x-gzip',
       'php' => 'application/x-httpd-php',
       'php4' => 'application/x-httpd-php',
       'php3' => 'application/x-httpd-php',
       'phtml' => 'application/x-httpd-php',
       'phps' => 'application/x-httpd-php-source',
       'js' => array('application/javascript', 'application/x-javascript'),
       'sit' => 'application/x-stuffit',
       'tar' => 'application/x-tar',
       'tgz' => array('application/x-tar', 'application/x-gzip-compressed'),
       'xhtml' => 'application/xhtml+xml',
       'xht' => 'application/xhtml+xml',             
       'bmp' => array('image/bmp', 'image/x-windows-bmp'),
       'gif' => 'image/gif',
       'jpeg' => array('image/jpeg', 'image/pjpeg'),
       'jpg' => array('image/jpeg', 'image/pjpeg'),
       'jpe' => array('image/jpeg', 'image/pjpeg'),
       'png' => array('image/png', 'image/x-png'),
       'tiff' => 'image/tiff',
       'tif' => 'image/tiff',
       'shtml' => 'text/html',
       'text' => 'text/plain',
       'log' => array('text/plain', 'text/x-log'),
       'rtx' => 'text/richtext',
       'rtf' => 'text/rtf',
       'xsl' => 'text/xml',
       'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
       'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
       'word' => array('application/msword', 'application/octet-stream'),
       'xl' => 'application/excel',
       'eml' => 'message/rfc822',
  
       // images
       'png' => 'image/png',
       'jpe' => 'image/jpeg',
       'jpeg' => 'image/jpeg',
       'jpg' => 'image/jpeg',
       'gif' => 'image/gif',
       'bmp' => 'image/bmp',
       'ico' => 'image/vnd.microsoft.icon',
       'tiff' => 'image/tiff',
       'tif' => 'image/tiff',
       'svg' => 'image/svg+xml',
       'svgz' => 'image/svg+xml',
  
       // archives
       //'zip' => array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
       'zip' => 'application/zip',
       'rar' => 'application/x-rar-compressed',
       'msi' => 'application/x-msdownload',
       'cab' => 'application/vnd.ms-cab-compressed',
  
       // audio/video
       'mid' => 'audio/midi',
       'midi' => 'audio/midi',
       'mpga' => 'audio/mpeg',
       'mp2' => 'audio/mpeg',
       'mp3' => array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
       'aif' => 'audio/x-aiff',
       'aiff' => 'audio/x-aiff',
       'aifc' => 'audio/x-aiff',
       'ram' => 'audio/x-pn-realaudio',
       'rm' => 'audio/x-pn-realaudio',
       'rpm' => 'audio/x-pn-realaudio-plugin',
       'ra' => 'audio/x-realaudio',
       'rv' => 'video/vnd.rn-realvideo',
       'wav' => array('audio/x-wav', 'audio/wave', 'audio/wav'),
       'mpeg' => 'video/mpeg',
       'mpg' => 'video/mpeg',
       'mpe' => 'video/mpeg',
       'qt' => 'video/quicktime',
       'mov' => 'video/quicktime',
       'avi' => 'video/x-msvideo',
       'movie' => 'video/x-sgi-movie',
  
       // adobe
       'pdf' => 'application/pdf',
       'psd' => array('image/vnd.adobe.photoshop', 'application/x-photoshop'),
       'ai' => 'application/postscript',
       'eps' => 'application/postscript',
       'ps' => 'application/postscript',
  
       // ms office
       'doc' => 'application/msword',
       'rtf' => 'application/rtf',
       'xls' => array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
       'ppt' => array('application/powerpoint', 'application/vnd.ms-powerpoint'),
  
       // open office
       'odt' => 'application/vnd.oasis.opendocument.text',
       'ods' => 'application/vnd.oasis.opendocument.spreadsheet',

       // auto cad
       'dwg' => 'image/vnd.dwg',
       'dxf' => 'image/vnd.dxf',
       'dwf' => 'drawing/x-dwf',
    );
  
    $ext = explode('.', $filename);
    $ext = strtolower(end($ext));

    if (array_key_exists($ext, $mime_types)) {
        return (is_array($mime_types[$ext])) ? $mime_types[$ext][0] : $mime_types[$ext];
    } else if (function_exists('finfo_open')) {
         if(file_exists($filename)) {
           $finfo = finfo_open(FILEINFO_MIME);
           $mimetype = finfo_file($finfo, $filename);
           finfo_close($finfo);
           return $mimetype;
         }
    }
     
    return 'application/octet-stream';
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php'; 

/* check if user logged else redirect to 404 */
global $current_user;
$current_user = wp_get_current_user();

if ( !function_exists( 'is_user_logged_in' ) ) { 
    require_once    $_SERVER['DOCUMENT_ROOT'] . '/wp-includes/pluggable.php'; 
} 

$current_user = wp_get_current_user();

// Stringa da decifrare
//$encryption  = "xa983QsgzYsYyBKy9o1vD5paMzLcTj/9Y+wUCN6fNLZJX2pJDZx11j9dLXpjfIteDFylnJnRyDIEsEIoC9VxdSVpEKgH78unPuxHn8vwpciGsKn66w==";

$encryption  = urldecode($_GET['f']);
$encryption = str_replace(' ','+',$encryption);
// echo "Stringa prima della decodifica: " . $encryption . "<br>";

/* check if isset PID (product ID) in GET else redirect to 404 */
if(!$_GET['pid']) {
    //echo 'errore pid<br>';
    global $wp_query;
    $wp_query->set_404();
    status_header( 404 );
    get_template_part( 404 ); exit();
}

/* function decrpyt */
// Stringa salt predefinita da usare sia lato API sia lato PHP sul sito
$decryption_key = URL_ENCRYPTION_KEY;
$ciphering = URL_CIPHERING;
$iv_length = openssl_cipher_iv_length($ciphering);
$options = 0;
$decryption_iv = URL_ENCRYPTION_IV;
$decryption = openssl_decrypt($encryption, $ciphering, $decryption_key, $options, $decryption_iv);

/* check if is isset LOCK in filename */
$chk = substr($decryption, strlen($decryption)-2);
if($chk != '|0' && $chk != '|1') {
    //echo 'errore isset lock<br>';
    global $wp_query;
    $wp_query->set_404();
    status_header( 404 );
    get_template_part( 404 ); exit();
}

/* check if file is LOCK and user are logged else redirect to 404 */
if($chk == '|1' && $current_user->ID < 1) {
    //echo 'errore tipo lock<br>';
    header('location: /log-in/');
    exit();
}

$decryption = substr_replace($decryption ,"",-2);
//$decryption = 'https://pimiaki.faac.help/storage/uploads/2022/11/Product_drawing_ASSA_ABLOY_RD300-3 RD300-4_en.dwg';

$mime = mime_type($decryption);

global $wpdb;
$data = array(
	'url_file' => $decryption,
	'language' => get_bloginfo('language'),
	'product_id' => intval($_GET['pid']),
    'download_type' => $_GET['dcat'],
	'user_id' => intval($current_user->ID),
    'lock' => str_replace('|','', $chk),
    'ip_address' => getUserIpAddr()
);
$insert = $wpdb->insert($wpdb->prefix.'download_stats', $data);

$decryption = str_replace(' ','%20',$decryption);

/* echo is_file($decryption);
exit(); */

/* header('Content-Type:'.$mime);
header("Content-Transfer-Encoding: Binary"); 
header("Content-disposition: attachment; filename=\"" . basename($decryption) . "\""); 
readfile($decryption); */

/* $path_parts = pathinfo($decryption);

print_r($path_parts); */

/* echo $decryption.'<br>';

$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
foreach (glob("*") as $decryption) {
    echo finfo_file($finfo, $decryption) . "<br>";
}
finfo_close($finfo); */

//header('location: '.$decryption);

header("Content-type: application/x-file-to-save"); 
header("Content-Disposition: attachment; filename=".basename($decryption));
ob_end_clean();
readfile($decryption);
exit;

/* header('Content-Description: File Transfer');
header('Content-Type:'.$mime);
header('Content-Disposition: attachment; filename='.basename($decryption));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($decryption));
ob_clean();
flush();
readfile($decryption);
exit(); */

//Product_drawing_ASSA_ABLOY_RD300-3 RD300-4_en.dwg
//https://pimiaki.faac.help/storage/uploads/2022/11/Product_drawing_ASSA_ABLOY_RD300-3 RD300-4_en.dwg

/* $_REQUEST['file'] = '"'.$decryption.'"';

if(!isset($_REQUEST['file']) || empty($_REQUEST['file'])) 
{
	header("HTTP/1.0 400 Bad Request");
	exit;
}

// sanitize the file request, keep just the name and extension
// also, replaces the file location with a preset one ('./myfiles/' in this example)
$file_path  = $_REQUEST['file'];
$path_parts = pathinfo($file_path);
$file_name  = $path_parts['basename'];
$file_ext   = $path_parts['extension'];
$file_path  = './myfiles/' . $file_name;

// allow a file to be streamed instead of sent as an attachment
$is_attachment = isset($_REQUEST['stream']) ? false : true;

// make sure the file exists
if (is_file($file_path))
{
	$file_size  = filesize($file_path);
	$file = @fopen($file_path,"rb");
	if ($file)
	{
		// set the headers, prevent caching
		header("Pragma: public");
		header("Expires: -1");
		header("Cache-Control: public, must-revalidate, post-check=0, pre-check=0");
		header("Content-Disposition: attachment; filename=\"$file_name\"");

        // set appropriate headers for attachment or streamed file
        if ($is_attachment) {
                header("Content-Disposition: attachment; filename=\"$file_name\"");
        }
        else {
                header('Content-Disposition: inline;');
                header('Content-Transfer-Encoding: binary');
        }

        // set the mime type based on extension, add yours if needed.
        $ctype_default = "application/octet-stream";
        $content_types = array(
                "exe" => "application/octet-stream",
                "zip" => "application/zip",
                "mp3" => "audio/mpeg",
                "mpg" => "video/mpeg",
                "avi" => "video/x-msvideo",
        );
        $ctype = isset($content_types[$file_ext]) ? $content_types[$file_ext] : $ctype_default;
        header("Content-Type: " . $ctype);

		//check if http_range is sent by browser (or download manager)
		if(isset($_SERVER['HTTP_RANGE']))
		{
			list($size_unit, $range_orig) = explode('=', $_SERVER['HTTP_RANGE'], 2);
			if ($size_unit == 'bytes')
			{
				//multiple ranges could be specified at the same time, but for simplicity only serve the first range
				//http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
				list($range, $extra_ranges) = explode(',', $range_orig, 2);
			}
			else
			{
				$range = '';
				header('HTTP/1.1 416 Requested Range Not Satisfiable');
				exit;
			}
		}
		else
		{
			$range = '';
		}

		//figure out download piece from range (if set)
		list($seek_start, $seek_end) = explode('-', $range, 2);

		//set start and end based on range (if set), else set defaults
		//also check for invalid ranges.
		$seek_end   = (empty($seek_end)) ? ($file_size - 1) : min(abs(intval($seek_end)),($file_size - 1));
		$seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)),0);
	 
		//Only send partial content header if downloading a piece of the file (IE workaround)
		if ($seek_start > 0 || $seek_end < ($file_size - 1))
		{
			header('HTTP/1.1 206 Partial Content');
			header('Content-Range: bytes '.$seek_start.'-'.$seek_end.'/'.$file_size);
			header('Content-Length: '.($seek_end - $seek_start + 1));
		}
		else
		  header("Content-Length: $file_size");

		header('Accept-Ranges: bytes');
    
		set_time_limit(0);
		fseek($file, $seek_start);
		
		while(!feof($file)) 
		{
			print(@fread($file, 1024*8));
			ob_flush();
			flush();
			if (connection_status()!=0) 
			{
				@fclose($file);
				exit;
			}			
		}
		
		// file save was a success
		@fclose($file);
		exit;
	}
	else 
	{
		// file couldn't be opened
		header("HTTP/1.0 500 Internal Server Error");
		exit;
	}
}
else
{
	// file does not exist
	//header("HTTP/1.0 404 Not Found");
	//exit;
} */

?>