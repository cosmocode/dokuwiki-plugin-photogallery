<?php
/**
 * Embed an image gallery
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Marco Nolletti
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('PHOTOGALLERY_REL')) define('PHOTOGALLERY_REL',DOKU_REL.'lib/plugins/photogallery/');
if(!defined('PHOTOGALLERY_PGIMG')) define('PHOTOGALLERY_PGIMG','phpThumb/pgImg.php');
if(!defined('PHOTOGALLERY_PGIMG_REL')) define('PHOTOGALLERY_PGIMG_REL',PHOTOGALLERY_REL.PHOTOGALLERY_PGIMG);
if(!defined('PHOTOGALLERY_PGIMG_FILE')) define('PHOTOGALLERY_PGIMG_FILE',realpath(dirname(__FILE__).'/'.PHOTOGALLERY_PGIMG));
if(!defined('PHOTOGALLERY_IMAGES')) define('PHOTOGALLERY_IMAGES',PHOTOGALLERY_REL.'images/');
require_once(DOKU_PLUGIN.'syntax.php');
require_once(DOKU_INC.'inc/search.php');
require_once(DOKU_INC.'inc/JpegMeta.php');
require_once(dirname(__FILE__).'/phpThumb/phpThumb.config.php');
define('PGIMG_EXE_PERM',0110); // Owner and group execute permission in octal notation

class syntax_plugin_photogallery extends DokuWiki_Syntax_Plugin {	

    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'substition';
    }

    /**
     * What about paragraphs?
     */
    function getPType(){
        return 'block';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 155;
    }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
				$this->Lexer->addSpecialPattern('----+ *photogallery(?: [ a-zA-Z0-9_]*)?-+\n.*?\n?----+', $mode, 'plugin_photogallery');
    }

    /**
     * Handle the match - parse the data
     *
     * @param   string       $match   The text matched by the patterns
     * @param   int          $state   The lexer state for the match
     * @param   int          $pos     The character position of the matched text
     * @param   Doku_Handler $handler The Doku_Handler object
     * @return  bool|array Return an array with all data you want to use in render, false don't add an instruction
     */
    function handle($match, $state, $pos, Doku_Handler $handler){
        global $ID;
				global $conf;
				
        $data = array();

        // get lines
        $lines = explode("\n", $match);
        array_pop($lines);

				// get command
        $cmd = array_shift($lines);
        $cmd = str_replace('photogallery', '', $cmd);
        $cmd = trim($cmd, '- ');
				if (!strpos('show|link|info',$cmd)) {
						$cmd = 'show';
				}
				$data['command'] = $cmd;

        // set the defaults
        $data['phpthumb']    = $this->getConf('use_phpThumb');
        $data['autoplay']    = $this->getConf('autoplay');
        $data['pw']          = $this->getConf('poster_width');
        $data['ph']          = $this->getConf('poster_height');
        $data['tw']          = $this->getConf('thumbnail_width');
        $data['th']          = $this->getConf('thumbnail_height');
        $data['iw']          = $this->getConf('image_width');
        $data['ih']          = $this->getConf('image_height');
        $data['panar']       = $this->getConf('panorama_ratio');
        $data['panw']        = $this->getConf('panorama_width');
        $data['panh']        = $this->getConf('panorama_height');
        $data['posteralign'] = $this->getConf('posteralign');
        $data['filter']      = '';
        $data['sort']        = $this->getConf('sort');
        $data['limit']       = 0;
        $data['offset']      = 0;
        //$data['fullsize']    = 0;
				$data['ns']          = getNS($ID);
				$this->_setConfOptions($data,$this->getConf('options'));

        // parse additional options
        $params = $this->getConf('options').','.$params;
        $params = preg_replace('/[,&\?]+/',' ',$params);
        $params = explode(' ',$params);
        foreach($params as $param){
            if($param === '') continue;
            if($param == 'titlesort'){
                $data['sort'] = 'title';
            }elseif($param == 'datesort'){
                $data['sort'] = 'date';
            }elseif($param == 'modsort'){
                $data['sort'] = 'mod';
            }elseif(preg_match('/^=(\d+)$/',$param,$match)){
                $data['limit'] = $match[1];
            }elseif(preg_match('/^\+(\d+)$/',$param,$match)){
                $data['offset'] = $match[1];
            }elseif(is_numeric($param)){
                $data['cols'] = (int) $param;
            }elseif(preg_match('/^(\d+)([xX])(\d+)$/',$param,$match)){
                if($match[2] == 'X'){
                    $data['iw'] = $match[1];
                    $data['ih'] = $match[3];
                }else{
                    $data['tw'] = $match[1];
                    $data['th'] = $match[3];
                }
            }elseif(strpos($param,'*') !== false){
                $param = preg_quote($param,'/');
                $param = '/^'.str_replace('\\*','.*?',$param).'$/';
                $data['filter'] = $param;
            }else{
                if(substr($param,0,2) == 'no'){
                    $data[substr($param,2)] = false;
                }else{
                    $data[$param] = true;
                }
            }
        }
				
				// Check phpThumb requirements
				if ($data['phpthumb'] == true){
						if (!$this->_phpThumbCheck()){
								msg($this->getLang('phpthumbdisabled'),2);
								$data['phpthumb'] = false;
						}
				}

        // parse info
        foreach($lines as $line) {
            // ignore comments
            preg_match('/^(.*?(?<![&\\\\]))(?:#(.*))?$/', $line, $matches);
            $line = $matches[1];
            $line = str_replace('\\#', '#', $line);
            $line = trim($line);
            if(empty($line)) continue;
            $line = preg_split('/\s*:\s*/', $line, 2);
//            $line = preg_split('/*:*/', $line, 1);
						if($line[0] == 'namespace') $line[0] = 'ns';
						if($line[0] == 'page') $line[0] = 'pg';

						if($line[0] == 'ns'){
								if(preg_match('/^https?:\/\//i',$line[1]))
										$data['rss'] = true;
								else
										$line[1] = resolve_id(getNS($ID),$line[1]);
						}
						if($line[0] == 'pg'){
								$line[1] = resolve_id(getNS($ID),$line[1]);
						}

						// decode height x width values [pti]size strings
						if(preg_match('/^([pti])(size)$/',$line[0],$type)){
								if(preg_match('/^(\d+)([xX])(\d+)$/',$line[1],$size)){
                    $data[$type[1].'w'] = $size[1];
                    $data[$type[1].'h'] = $size[3];
								}
						}
						
						// handle negated options, converts "!crop" to "crop = false"
						if (!$line[1]){
								if (preg_match('/^\!.{1,}/', $line[0], $matches))
									$line[0] = substr($matches[0],1);
								else
									$line[1] = true;
						}
//           $column = $this->dthlp->_column($line[0]);
						$data [$line[0]]=$line[1];
            // if(isset($matches[2])) {// NOM da verificare
                // $column['comment'] = $matches[2];
            // }
            // if($column['multi']) {
                // if(!isset($data[$column['key']])) {
                    // // init with empty array
                    // // Note that multiple occurrences of the field are
                    // // practically merged
                    // $data[$column['key']] = array();
                // }
                // $vals = explode(',', $line[1]);
                // foreach($vals as $val) {
                    // $val = trim($this->dthlp->_cleanData($val, $column['type']));
                    // if($val == '') continue;
                    // if(!in_array($val, $data[$column['key']])) {
                        // $data[$column['key']][] = $val;
                    // }
                // }
            // } else {
// //                $data[$column['key']] = $this->dthlp->_cleanData($line[1], $column['type']);
            // }
//            $properties[$column['key']] = $column;
        }
        // return array(
            // 'data' => $data, 'command' => $command
        // ); // not utf8_strlen
				
				// If in link mode, read instructions from linked page
				if ($cmd == 'link'){
					$page = $data['pg'];
					if (page_exists($page)){
						$instr = p_cached_instructions(wikiFN($page),false,$page);
					}
					if (isset($instr)){
						foreach($instr as $sec){ //NOM forse si può usare array search
							if ($sec[0] == 'plugin'){
								if ($sec[1][0] == 'photogallery'){
									$rdata = $sec[1][1];
								}
							}
							if (isset($rdata)){
								$data['ns'] = $rdata['ns'];
								foreach ($rdata as $key => $value){
									if ((!isset($data[$key])) and (isset($rdata[$key]))){
										$data[$key] = $value;
									}
								}
								break;
							}
						}
					}
					else{
//						unset($data['pg']);
					}
				}
				return $data;
    }

    /**
     * Create output or save the data
     *
     * @param   $format   string        output format being rendered
     * @param   $renderer Doku_Renderer the current renderer object
     * @param   $data     array         data created by handler()
     * @return  boolean                 rendered correctly?
     */
    function render($mode, Doku_Renderer $R, $data){
        global $ID;
				global $conf;
				
				$cmd = $data['command'];
        if($mode == 'xhtml'){

						if($this->_auth_check($data)){
								if($cmd == 'info')
										$this->_info_page($R, $data);
								else{
										$R->info['cache'] &= $data['cache'];
										$this->_photo_gallery($data, $R); // Start gallery
								}
						}
						elseif($cmd == 'show')
								$R->doc .= '<div class="nothing">'.$this->getLang('notauthorized').'</div>';
						elseif($cmd == 'link')
								dbg($this->getLang('notauthorized'));
						return true;
        }elseif($mode == 'metadata'){ // NOM da rivedere
            $rel = p_get_metadata($ID,'relation',METADATA_RENDER_USING_CACHE);
            $img = $rel['firstimage'];
            if(empty($img)){
                $files = $this->_findimages($data);
            }
            return true;
        }
        return false;
		}
		
		function _phpThumbCheck(){
				$fperm = fileperms(PHOTOGALLERY_PGIMG_FILE);
				if (($fperm & PGIMG_EXE_PERM) != PGIMG_EXE_PERM){
						msg($this->getLang('phpthumbexecerror'),-1);
						if (@chmod(PHOTOGALLERY_PGIMG_FILE, $fperm | PGIMG_EXE_PERM)){
								msg($this->getLang('phpthumbexecpermset'),1);
								return true;
						}
						else{
								msg($this->getLang('phpthumbpermseterror'),-1);
								return false;
						}
				}
				return true;
		}

		function _info_page($R, $data){
				$R->header('PhotoGallery info page',2,0);
				$R->table_open();
				$this->_info_row($R,'Plugin info','Value',' ',true);
				$info = $this->getInfo();
				$this->_info_row($R,'Plugin version',$info['date']);
				$this->_info_row($R,'Author',$info['author']);
				$this->_info_row($R,'Server parameters','Value','Status',true);
				$this->_info_row($R,'Current PHP version',phpversion());
				$this->_info_row($R,'Running webserver',htmlentities($_SERVER['SERVER_SOFTWARE']));
				$ok = extension_loaded('exif');
				$this->_info_row($R,'EXIF extension',($ok ? '' : 'not').' loaded',$ok);
				$ok = extension_loaded('curl');
				$this->_info_row($R,'CURL extension',($ok ? '' : 'not').' loaded',$ok);
				$ok = extension_loaded('exif');
				$this->_info_row($R,'IMAGICK extension',($ok ? '' : 'not').' loaded',$ok);
				$ok = extension_loaded('gd');
				$this->_info_row($R,'GD extension',($ok ? '' : 'not').' loaded',$ok);
				if($ok){
						$info = gd_info();
						foreach($info as $key => $value) {
								$this->_info_row($R,'|-- '.$key,$value);
						}
				}
				$this->_info_row($R,'phpThumb requirements','Value','Status',true);
				$arr = array ('exec','system','shell_exec','passthru');
				$info = explode(',',@ini_get('disable_functions'));
				for ($i = 0; $i<count($info); $i++){
						if (array_search($info[$i],$arr) === false){
								array_splice($info,$i,1);
								$i--;
						}
				}
				$ok = (count($info) < count($arr));
				$info = implode(', ',$info);
				$this->_info_row($R,'Important disabled functions',$info,$ok);
				$info = fileperms(PHOTOGALLERY_PGIMG_FILE) & 0xFFF;
				$ok = (($info & PGIMG_EXE_PERM) == PGIMG_EXE_PERM);
				$this->_info_row($R,'pgImg.php execute permissions',sprintf('%o',$info),$ok);
				$ok = $data['phpthumb'];
				$this->_info_row($R,'phpThumb state',($ok ? '' : 'not').' enabled');
				$R->table_close();
		}

		function _info_row($R,$item, $value, $state = null, $header = false){
				if ($header)
						$R->tablethead_open();
				$R->tablerow_open();
				$this->_info_cell($R,$item,$header);
				$this->_info_cell($R,$value,$header);
				if(is_bool($state))
						$this->_info_cell($R,$state ? "ok" : "error",$header);
				else
						$this->_info_cell($R,$state,$header);
				$R->tablerow_close();
				if ($header)
						$R->tablethead_close();
		}
		
		function _info_cell($R,$text, $header = false){
				if ($header)
						$R->tableheader_open();
				else
						$R->tablecell_open();
				$R->cdata($text);
				if ($header)
						$R->tableheader_close();
				else
						$R->tablecell_close();
		}

    /**
     * Does the gallery formatting
     */
    function _photo_gallery($data, $R){
        global $conf;
        global $lang;
				global $ID;
				
				//dbg($data);
        $cmd = $data['command'];
				if (!$data['rss']){
					if(($cmd == 'show') and (!$this->_media_folder_exists($data['ns']))){
							$R->doc .= '<div class="nothing">'.sprintf($this->getLang('nsnotexists'),$data['ns']).'</div>';
							return true;
					}elseif (($cmd == 'link') and (!page_exists($data['pg']))){
							$R->doc .= '<div class="nothing">'.sprintf($this->getLang('pgnotexists'),$data['pg']).'</div>';
							return true;
					}
				}

        $files = $this->_findimages($data);
	
        // anything found?
        if(!count($files)){
            $R->doc .= '<div class="nothing">'.$lang['nothingfound'].'</div>';
            return;
        }

				// in not exists create in the media folder a zip file containing all the images and link it
				if (isset($data['zipfile'])){
						$zip = $data['ns'].":".$data['zipfile'];
						$this->_createzipfile($files, mediaFN($zip));
						$data['ziplink'] = $R->internalmedia($zip,$this->getLang('lnk_download'),null,null,null,null,'linkonly',true);
				}

				// output pg-container
				$R->doc .= '<div class="pg-container">'.DOKU_LF;
				
				// output pg-poster and pg-description
				if ($data['posteralign'] == 'right'){
					$this->_description($files,$data,$R);
					$this->_poster($files,$data,$R);
				}
				else{
					$this->_poster($files,$data,$R);
					$this->_description($files,$data,$R);
				}
				
				// Close container
				$R->doc .= '</div>'.DOKU_LF;
				return;
    }
		
    // function _showData($data, $R) { // NOM da vedere

        // // if(method_exists($R, 'startSectionEdit')) {
            // // $data['classes'] .= ' ' . $R->startSectionEdit($data['pos'], 'plugin_data');

            // // $class_name = hsc(sectionID($key, $class_names));
                    // // $ret .= $this->dthlp->_formatData($data['cols'][$key], $val[$i], $R);
        // // if(method_exists($R, 'finishSectionEdit')) {
            // // $R->finishSectionEdit($data['len'] + $data['pos']);
        // // }
    // }

    /**
     * Gather all photos matching the given criteria
     */
    function _findimages(&$data){
        global $conf;
        $files = array();

        // is a media RSS feed ?
        if($data['rss']){
            $files = $this->_loadRSS($data['ns']);
        }else{
            $dir = utf8_encodeFN(str_replace(':','/',$data['ns']));
            // all possible images for the given namespace
						$depth = $data['recursive'] ? 0 : 1;
						search($files,
									 $conf['mediadir'],
									 'search_media',
									 array('depth'=>$depth),
									 $dir);
        }

        // done, yet?
        $len = count($files);
        if(!$len) return $files;
        if($len == 1) return $files;
        // filter images
        for($i=0; $i<$len; $i++){
						if($data['fullsize'] == true)
							$files[$i]['fullsize'] = true;
						$fname = $files[$i]['file'];
						if (preg_match('/\_([a-z]+?)\_\.(jpe?g|gif|png)$/',$fname,$matches)){
								$modifier = $matches[1];
								if(($modifier == 'fullsize') || ($data['fullsize'] == 1))		// Show in full size
										$files[$i]['fullsize'] = true;
								elseif($modifier == 'poster')			// Is a video poster image, remove from list
										$files[$i]['isimg'] = false;
						}
            if(!$files[$i]['isimg']){
								if(preg_match('/(.*?)\.(avi|mov|mp4)$/',$fname,$matches)){	// Is a video
										$files[$i]['isvid'] = true;
										$poster = getNS($files[$i]['id']).':'.$matches[1].'_poster_.jpg';
										if(in_array($poster,array_column($files,'id'))) // Check if poster exists
												$files[$i]['poster'] = $poster;
								}
								else{
//		               unset($files[$i]); // this is faster, because RE was done before
										array_splice($files, $i, 1); // unset will not reindex the array, so putting the poster on first position fails
										$len--;
										$i--;
								}
            }
						else{
								if($data['filter']){
									if(!preg_match($data['filter'],noNS($files[$i]['id'])))
											unset($files[$i]); // NOM da verificare unset come sopra se si decide di usare filter
							}
            }
        }
				if($len<1) return $files;

        // sort?
        if($data['sort'] == 'random'){
            shuffle($files);
        }else{
            if($data['sort'] == 'date'){
                usort($files,array($this,'_datesort'));
            }elseif($data['sort'] == 'mod'){
                usort($files,array($this,'_modsort'));
            }elseif($data['sort'] == 'title'){
                usort($files,array($this,'_titlesort'));
            }
            // reverse?
            if($data['reverse']) $files = array_reverse($files);
        }

        // offset?
        if($data['offset']){
						$offset = $data['offset'];
						$files = array_slice($files,$offset);
				} else{
						$offset = 0;
				}

				// puts poster element in first array position
				$i = array_search($data['posterimg'], array_column($files, 'file'));
				if ($i != $offset){
					$tmp = $files[$offset];
					$files[$offset] = $files[$i];
					$files[$i] = $tmp;
				}

        // limit?
        if($data['limit'])
						$files = array_slice($files,0,$data['limit']);

        return $files;
    }

    /**
     * Loads images from a MediaRSS or ATOM feed
     */
    function _loadRSS($url){
        require_once(DOKU_INC.'inc/FeedParser.php');
        $feed = new FeedParser();
        $feed->set_feed_url($url);
        $feed->init();
        $files = array();

        // base url to use for broken feeds with non-absolute links
        $main = parse_url($url);
        $host = $main['scheme'].'://'.
                $main['host'].
                (($main['port'])?':'.$main['port']:'');
        $path = dirname($main['path']).'/';

        foreach($feed->get_items() as $item){
            if ($enclosure = $item->get_enclosure()){
                // skip non-image enclosures
                if($enclosure->get_type()){
                    if(substr($enclosure->get_type(),0,5) != 'image') continue;
                }else{
                    if(!preg_match('/\.(jpe?g|png|gif)(\?|$)/i',
                       $enclosure->get_link())) continue;
                }

                // non absolute links
                $ilink = $enclosure->get_link();
                if(!preg_match('/^https?:\/\//i',$ilink)){
                    if($ilink{0} == '/'){
                        $ilink = $host.$ilink;
                    }else{
                        $ilink = $host.$path.$ilink;
                    }
                }
                $link = $item->link;
                if(!preg_match('/^https?:\/\//i',$link)){
                    if($link{0} == '/'){
                        $link = $host.$link;
                    }else{
                        $link = $host.$path.$link;
                    }
                }

                $files[] = array(
                    'id'     => $ilink,
                    'isimg'  => true,
                    'file'   => basename($ilink),
                    // decode to avoid later double encoding
                    'title'  => htmlspecialchars_decode($enclosure->get_title(),ENT_COMPAT),
                    'desc'   => strip_tags(htmlspecialchars_decode($enclosure->get_description(),ENT_COMPAT)),
                    'width'  => $enclosure->get_width(),
                    'height' => $enclosure->get_height(),
                    'mtime'  => $item->get_date('U'),
                    'ctime'  => $item->get_date('U'),
                    'detail' => $link,
                );
            }
        }
        return $files;
    }

    /**
     * usort callback to sort by file lastmodified time
     */
    function _modsort($a,$b){
        if($a['mtime'] < $b['mtime']) return -1;
        if($a['mtime'] > $b['mtime']) return 1;
        return strcmp($a['file'],$b['file']);
    }

    /**
     * usort callback to sort by EXIF date
     */
    function _datesort($a,$b){
        $da = $this->_meta($a,'cdate');
        $db = $this->_meta($b,'cdate');
        if($da < $db) return -1;
        if($da > $db) return 1;
        return strcmp($a['file'],$b['file']);
    }

    /**
     * usort callback to sort by EXIF title
     */
    function _titlesort($a,$b){
        $ta = $this->_meta($a,'title');
        $tb = $this->_meta($b,'title');
        return strcmp($ta,$tb);
    }

    /**
     * Does the lightgallery gallery formatting
     */
    function _lightgallery($files,$data,$pgid){
				$ret = '';
				$ret .= '<ul id="'.$pgid.'" class="pg-show">'.DOKU_LF;

        $page = 0;

        // build gallery
				$close_pg = false;

				$i = 0;
				foreach($files as $img){
						$ret .= $this->_image($img,$data,$i);
						$i++;
				}

				// Close containers
				$ret .= '</ul>'.DOKU_LF;
				return $ret;
		}

    /**
     * Defines how a poster should look like
     */
    function _poster($files,$data,$R){
				$pgid = 'pg-'.substr(md5($data['ns']),4);
				if ($data['posteralign'] == 'right')
					$R->doc .= '<div class="pg-poster pg-right">'.DOKU_LF;
				else
					$R->doc .= '<div class="pg-poster pg-left">'.DOKU_LF;

				$img = $files[0];
				$cmd = $data['command'];

        // calculate poster size
				$w = $data['pw'];
				$h = $data['ph'];

				$dim = array('w'=>$w,'h'=>$h);

        //prepare link attributes
        $a = array();
				if ($cmd == 'show'){
						$href = '';
						$a['data-pg-id'] = $pgid;
						$a['class'] = 'pg-start';
				}
				else{
						$href = wl($data['pg'], 'gallery0#lg=1&amp;slide=0');
				}
        $aatt = buildAttributes($a);

        //prepare img attributes
        $i           = array();
        $src = ml($img['id'],$dim);

        $i['width']    = $w;
        $i['height']   = $h;
//        $i['border']   = 0;
        $i['alt']  = $this->_meta($img,'title');
        $iatt = buildAttributes($i);
//				$src = ml($img['id'],$dim);

				// Generate output
        $R->doc .= '<a href="'.$href.'" '.$aatt.'>'.DOKU_LF;
				$R->doc .= '<img src="'.$src.'" '.$iatt.'/>'.DOKU_LF;
				$R->doc .= '<div class="pg-zoom">';
				$R->doc .= '<img src="'.PHOTOGALLERY_IMAGES.'/zoom.png">';
				$R->doc .= '</div>'.DOKU_LF;
        $R->doc .= '</a>'.DOKU_LF;

				if ($cmd == 'show'){
					$R->doc .= $this->_lightgallery($files,$data,$pgid);

					// Call lightGallery init function
					$ch = strval(intval($data['th'])+20);
					$auto = $data['autoplay'] ? 'true' : 'false';
					$R->doc .= '<script type="text/javascript">/*<![CDATA[*/'.DOKU_LF;
					$R->doc .= 'jQuery(function(){';
					$R->doc .= 'InitPgGallery('.$data['tw'].','.$ch.','.$auto.');';
					$R->doc .= '});'.DOKU_LF;
					$R->doc .= '/*!]]>*/</script>'.DOKU_LF;
					
					// Override styles to match thumb size
					$R->doc .= '<style>.lg-outer.lg-pull-caption-up.lg-thumb-open .lg-sub-html {bottom:'.$ch.'px;}</style>';
				}
				$R->doc .= '</div>'.DOKU_LF;
    }

    /**
     * Defines how a description  should look like
     */
    function _description($files,$data,$R){
				$imgcnt = 0;
				$vidcnt = 0;
				foreach ($files as $file){
					if ($file['isimg'])
						$imgcnt++;
					elseif ($file['isvid'])
						$vidcnt++;
				}
				if ($data['posteralign'] == 'right')
					$R->doc .= '<div class="pg-description pg-left">'.DOKU_LF;
				else
					$R->doc .= '<div class="pg-description pg-right">'.DOKU_LF;
				
				$R->header($data['title'],2,0);
				$R->doc .= '<p>'.$data['description'].'</p>';
				$R->doc .= '<p>';
				$info = '';
				$this->_addString($info,$imgcnt,sprintf($this->getLang('imagescnt'),$imgcnt));
				$this->_addString($info,$vidcnt,sprintf($this->getLang('videoscnt'),$vidcnt),', ');
				$R->doc .= $info;
				if (isset($data['ziplink'])){
						$R->doc .= ' - '.$data['ziplink'];
				}
				$R->doc .= '</p>';
				$R->doc .= '<p align="left"><i>'.$data['copyright'].'</i></p>'.DOKU_LF;
				$R->doc .= '</div>'.DOKU_LF;
		}
		
		/**
     * Defines the lightGallery images markup
     */
    function _image(&$img,$data,$idx){
				global $conf;
				if ($data['phpthumb'] == true){
						// Warning src should be the last defined parameters
						$tpar['w'] = $data['tw'];
						$tpar['h'] = $data['th'];
						$ipar = array();
						$ipar['w'] = $data['iw'];
						$ipar['h'] = $data['ih'];
						if($img['isvid']){
								$tpar['zc'] = 'C'; // Crop to given size
								if($img['poster']){
										$tpar['fltr[0]'] = 'over|../images/video_frame.png';
										$tpar['src'] = DOKU_BASE.$conf['savedir'].'/media/'.idfilter($img['poster']);
										$ipar['src'] = $tpar['src'];
								} else{
										$tpar['src'] = PHOTOGALLERY_IMAGES.'video_thumb.png';
										$ipar['src'] = PHOTOGALLERY_IMAGES.'video_poster.jpg';;
								}
								$vsrc = ml($img['id']);
						} else{
								$mw = (int) $this->_meta($img,'width');
								$mh = (int) $this->_meta($img,'height');
								$img_ar = ($mw > $mh ? $mw/$mh : $mh/$mw);
								if (preg_match('/([0-9]+):([0-9]+)/',$data['panar'],$matches))
									$max_ar = $matches[1]/$matches[2];
								if ($img_ar > $max_ar){ // Test for panorama aspect ratio
										$tpar['far'] = 1; // Force aspect ratio
										if ($mw > $mh){ // Landscape
												$tpar['w'] = floor($data['th'] * 0.6 * $img_ar);
												$cropw = floor(($tpar['w'] - $data['tw']) / 2);
												$tpar['fltr[0]'] = "crop|$cropw|$cropw";
												$tpar['fltr[1]'] = 'over|../images/pano_landscape.png';
										} else{ // Portrait or square
												$tpar['h'] = floor($data['tw'] * 0.6 * $img_ar);
												$croph = floor(($tpar['h'] - $data['th']) / 2);
												$tpar['fltr[0]'] = "crop|0|0|$croph|$croph";
												$tpar['fltr[1]'] = 'over|../images/pano_portrate.png';
										}
										$ipar['w'] = $data['panw'];
										$ipar['h'] = $data['panh'];
								} else{  // Normal image
										$tpar['zc'] = 'C'; // Crop to given size
								}
								if ($img['fullsize']){  // Override image size for fullsize
										$tpar['fltr[2]'] = 'over|../images/image_fullsize.png';
										$ipar['w'] = $mw;
										$ipar['h'] = $mh;
								} 
								if ($data['rss'])
										$tpar['src'] = $img['id'];
								else
										$tpar['src'] = DOKU_BASE.$conf['savedir'].'/media/'.idfilter($img['id']);
								$ipar['src'] = $tpar['src'];
						}
						$isrc = htmlspecialchars(pgThumbURL($ipar, PHOTOGALLERY_PGIMG_REL));
						$tsrc = htmlspecialchars(pgThumbURL($tpar, PHOTOGALLERY_PGIMG_REL));
				} else{ // Use Dokuwiki media link and cache
						// prepare dimensions
						$tdim = array('w'=>$data['tw'],'h'=>$data['th']);
						if ($img['fullsize']){
								$cropw = (int) $this->_meta($img,'width');
								$croph = (int) $this->_meta($img,'height');
						} else{
								$cropw = $data['iw'];
								$croph = $data['ih'];
						} 
						$iw = (int) $this->_meta($img,'width');
						$ih = (int) $this->_meta($img,'height');
						$idim = array();
						// crop to lightbox dimensions
						if($iw > $cropw || $ih > $croph){
								$ratio = $this->_ratio($img,$cropw,$croph);
								$iw = floor($iw * $ratio);
								$ih = floor($ih * $ratio);
						}
						$idim = array('w'=>$iw,'h'=>$ih);
						if($img['isvid']){
								$vsrc = ml($img['id'],$tdim);
								$tsrc = PHOTOGALLERY_IMAGES.'video_thumb.png';
								if($img['poster']){
										$isrc = ml($img['poster'],$idim);
								} else{
										$isrc = PHOTOGALLERY_IMAGES.'video_poster.jpg';
								}
						} else{
								$isrc = ml($img['id'],$idim);
								$tsrc = $isrc;
						};

						//prepare image attributes
						// $ia  = array();
						// $ia['width'] = $iw;
						// $ia['height'] = $ih;
						// $ia['border'] = 0;
						// $ia['title'] = $this->_caption($img,$data);
						// $iatt = buildAttributes($ia); //NOM not used yet
				}
				// prepare attributes
				$ta = array();
				$ta['alt'] = $this->_caption($img,$data);
				$tatt = buildAttributes($ta);
				// HTML rendering
  			$ret ='';
				$style =' style="display:none;"';
				$style = ''; //NOM: controllare
				$video = '';
				if($img['isvid']){
						$video .= '<div id="video'.$idx.'" style="display:none;">'.DOKU_LF;
						$video .= '<video class="lg-video-object lg-html5" controls preload="metadata">';
						$video .= '<source src="'.$vsrc.'" type="video/mp4">';
						$video .= 'HTML5 video not supported.';
						$video .= '</video>'.DOKU_LF;
						$video .= '</div>'.DOKU_LF;
						$ret .= '<li data-poster="'.$isrc.'" data-html="#video'.$idx.'">'.DOKU_LF;
				} else{
						$ret .= '<li data-src="'.$isrc.'"'.$style.'>'.DOKU_LF;
				}
				if ($idx < 25){
						$ret .= '<img class="pg-preload" src="'.$tsrc.'" '.$tatt.'/>'.DOKU_LF;
//				$ret .= '<img src="'.$tsrc.'" '.$tatt.'/>'.DOKU_LF;
				}
				else{
						$ret .= '<img class="pg-preload" data-src="'.$tsrc.'" '.$tatt.'/>'.DOKU_LF;
				};
				if ($idx < 5){
						$ret .= '<img class="pg-preload" style="display:none;" src="'.$isrc.'"/>'.DOKU_LF;
				};
				$ret .= $video;
        $ret .= '</li>'.DOKU_LF;
        return $ret;
    }

    /**
     * Return the metadata of an item
     *
     * Automatically checks if a JPEGMeta object is available or if all data is
     * supplied in array
     */
    function _meta(&$img,$opt){
        if($img['meta']){
            // map JPEGMeta calls to opt names

            switch($opt){
                case 'title':
                    return $img['meta']->getField('Simple.Title');
                case 'desc':
                    return $img['meta']->getField('Iptc.Caption');
                case 'cdate':
                    return $img['meta']->getField('Date.EarliestTime');
                case 'width':
                    return $img['meta']->getField('File.Width');
                case 'height':
                    return $img['meta']->getField('File.Height');
                default:
                    return '';
            }
        }else{
            // just return the array field
            return $img[$opt];
        }
    }

    /**
     * Calculates the multiplier needed to resize the image to the given
     * dimensions
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    function _ratio(&$img,$maxwidth,$maxheight=0){
        if(!$maxheight) $maxheight = $maxwidth;

        $w = $this->_meta($img,'width');
        $h = $this->_meta($img,'height');

        $ratio = 1;
        if($w >= $h){
            if($w >= $maxwidth){
                $ratio = $maxwidth/$w;
            }elseif($h > $maxheight){
                $ratio = $maxheight/$h;
            }
        }else{
            if($h >= $maxheight){
                $ratio = $maxheight/$h;
            }elseif($w > $maxwidth){
                $ratio = $maxwidth/$w;
            }
        }
        return $ratio;
    }

    /**
     * Return the caption for the image
     */
    function _caption($img,$data){
				$ret = '';
				if ($data['showtitle']){
						$title = $this->_meta($img,'title');
						if(isset($title)){
							$ret .= '<h4>'.hsc($title).'</h4>';
						}
				}
				if ($data['showinfo']){
						$ret .= $this->_exif($img);
				}
				if ($data['showfname']){
						$ret .= '<p>'.hsc($img['file']).'</p>';
				}
				return $ret;
		}
	
    /**
     * Return the EXIF data for the image
     */
		function _exif($img){
				// Read EXIF data
				$jpeg = $img['meta'];
				$ret = '';
        if($jpeg){
						$make  = $jpeg->getField(array('Exif.Make','Exif.TIFFMake'));
						$model = $jpeg->getField(array('Exif.Model','Exif.TIFFModel'));
						$model = preg_replace('/^'.$make.' /','',$model);
						$shutter = $jpeg->getShutterSpeed();
						$fnumber = $jpeg->getField(array('Exif.FNumber'));
						$iso = $jpeg->getField(array('Exif.ISOSpeedRatings'));
						$date = $jpeg->getDateField('EarliestTimeStr');
						$yy = substr($date ,0,4);
						$mm = substr($date ,5,2);
						$dd = substr($date ,8,2);
						$date = $dd.'/'.$mm.'/'.$yy;
						$ret .= $date;
						$this->_addString($ret,$make.$model,$make.' '.$model, ' - ');
						$this->_addString($ret,$shutter,$shutter.'s',', ');
						$this->_addString($ret,$fnumber,'f/'.$fnumber,', ');
						$this->_addString($ret,$iso,'ISO '.$iso,', ');
						$this->_addString($ret,$ret,null,null,'<p>','</p>');
				}
				return $ret;
		}

    /**
     * Creates a compressed zip file
     */
		function _createzipfile($files,$zipfile,$overwrite = false) {
			//if the zip file already exists and overwrite is false, return false
			if(file_exists($zipfile) && !$overwrite) return false;
			if(count($files)) {
				//create the archive
				$zip = new ZipArchive();
				if($zip->open($zipfile,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
					return false;
				}
				//add the files
				foreach($files as $img) {
					$file = mediaFN($img['id']);
					$zip->addFile($file,basename(dirname($file)).'/'.basename($file));
				}
				
				//close the zip -- done!
				$zip->close();
				
				//check to make sure the file exists
				return file_exists($zipfile);
			}
			else {
				return false;
			}
		}

    /**
     * Check ACLs
     */
		function _auth_check($data){
				global $USERINFO;
				global $auth;
				global $conf;

				if(!$auth) return false;
				$user .= $_SERVER['REMOTE_USER'];

				if(is_null($user)) return false;
				$groups = (array) $USERINFO['grps'];
				$authlist = $data['authlist'];
				if (isset($authlist)){
					$authlist .= ','.$conf['superuser'];
					return auth_isMember($authlist, $user, $groups);
				}
				else
					return true;
		}

    /**
     * Return if a namespace has exists as media folder
     */
		function _media_folder_exists($ns){
				global $conf;
				return is_dir($conf['mediadir'].'/'.utf8_encodeFN(str_replace(':','/',$ns)));
		}
		
    /**
     * Sets additional comma separated options
     */
		function _setConfOptions(&$data, $optstr){
				$opts = explode(',', $optstr);
				foreach ($opts as $opt)
					$data[trim($opt)] = true;
		}

		/**
     * Adds a string to $source only if $check is true.
     */
		function _addString(&$source, $check, $value = '', $separator = '', $prefix = '', $suffix = ''){
				if($check){
						if($source)
								$source .= $separator;
						$source .= $value;
						$source = $prefix.$source.$suffix;
				}
		}
}
//				$jpeg = new JpegMeta(mediaFN($img['id']));
				// if($ext == 'jpg' || $ext == 'jpeg') {
                // //try to use the caption from IPTC/EXIF
                // require_once(DOKU_INC.'inc/JpegMeta.php');
                // $jpeg = new JpegMeta(mediaFN($src));
                // if($jpeg !== false) $cap = $jpeg->getTitle();
				
				// $exif_data = exif_read_data($path,'IFD0',0); 
				// $emake = $exif_data['Make'];
				// $emodel = $exif_data['Model'];
				// $emodel = str_replace($emake,"",$emodel);
				// $eexposuretime = $exif_data['ExposureTime'];
		// //							$efnumber = $exif_data['FNumber'];
				// $efnumber = $exif_data['COMPUTED']['ApertureFNumber'];
				// $eiso = $exif_data['ISOSpeedRatings'];
				// $edate = $exif_data['DateTimeOriginal']; 
				// $yy = substr($edate ,0,4);
				// $mm = substr($edate ,5,2);
				// $dd = substr($edate ,8,2);
				// $h =  substr($edate ,11,2);
				// $m =  substr($edate ,14,2);
				// $s =  substr($edate ,17,2);
				// $date = $dd.'/'.$mm.'/'.$yy;
				// $time = $h.':'.$m.':'.$s;
				// return $date." - ".$emake." ".$emodel.", ".$eexposuretime."s, ".$efnumber.", ISO ".$eiso;
           // $page = $this->_apply_macro($page, $parent_id);
            // resolve_pageid(getNS($parent_id), $page, $exists); // resolve shortcuts and clean ID
            // if (auth_quickaclcheck($page) >= AUTH_READ)
                // $pages[] = $page;
  
	    // function _showname($img,$data){

        // //prepare link
        // $lnk = ml($img['id'],array('id'=>$ID),false);

        // // prepare output
        // $ret .= hsc($img['file']);