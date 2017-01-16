<?php
/**
 * Embed an image gallery
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Marco Nolletti <mnolletti@gmail.com>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('DOKU_PHOTOGALLERY')) define('DOKU_PHOTOGALLERY','/home/lib/plugins/photogallery/');
require_once(DOKU_PLUGIN.'syntax.php');
require_once(DOKU_INC.'inc/search.php');
require_once(DOKU_INC.'inc/JpegMeta.php');

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
//        $this->Lexer->addSpecialPattern('\{\{photogallery>[^}]*\}\}',$mode,'plugin_photogallery');
				$this->Lexer->addSpecialPattern('----+ *photogallery(?: [ a-zA-Z0-9_]*)?-+\n.*?\n----+', $mode, 'plugin_photogallery');
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
				if (!strpos('show|link',$cmd)) {
						$cmd = 'show';
				}
				$data['command'] = $cmd;

        // set the defaults
        $data['pw']          = $this->getConf('poster_width');
        $data['ph']          = $this->getConf('poster_height');
        $data['tw']          = $this->getConf('thumbnail_width');
        $data['th']          = $this->getConf('thumbnail_height');
        $data['iw']          = $this->getConf('image_width');
        $data['ih']          = $this->getConf('image_height');
        $data['posteralign'] = $this->getConf('posteralign');
        $data['filter']      = '';
        $data['sort']        = $this->getConf('sort');
        $data['limit']       = 0;
        $data['offset']      = 0;
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
            // if(isset($matches[2])) {
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
								$data['ns'] = $rdada['ns'];
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
//dbg($conf);
				 //dbg($data);
				 //return false;
				$cmd = $data['command'];
        if($mode == 'xhtml'){
						if($this->_auth_check($data)){
								$R->info['cache'] &= $data['cache']; //NOM capire
								$this->_photo_gallery($data, $R); // Start gallery
						}
						elseif($cmd == 'show'){
								$R->doc .= '<div class="nothing">'.$this->getLang('notauthorized').'</div>';
						};
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

    /**
     * Does the gallery formatting
     */
    function _photo_gallery($data, $R){
        global $conf;
        global $lang;
				global $ID;
				
				//dbg($data);
				if ($data['crop'])
					$R->doc .= 'Ciao'; // NOM da controllare
				
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

        //return '<div class="gallery'.$align.'"'.$xalign.'>'.$pgret.$R->doc.'<div class="clearer"></div></div>';
				return;
    }
		
    // function _showData($data, $R) {

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

        // http URLs are supposed to be media RSS feeds
        if($data['rss']){
            $files = $this->_loadRSS($data['ns']);
            $data['rss'] = true;
        }else{
            $dir = utf8_encodeFN(str_replace(':','/',$data['ns']));
            // all possible images for the given namespace
            if(is_file($conf['mediadir'].'/'.$dir)){ //NOM da togliere, file singolo
                require_once(DOKU_INC.'inc/JpegMeta.php');
                $files[] = array(
                    'id'    => $data['ns'],
                    'isimg' => preg_match('/\.(jpe?g|gif|png)$/',$dir),
                    'file'  => basename($dir),
                    'mtime' => filemtime($conf['mediadir'].'/'.$dir),
                    'meta'  => new JpegMeta($conf['mediadir'].'/'.$dir)
                );
//                $data['_single'] = true;
            }else{
                $depth = $data['recursive'] ? 0 : 1;
                search($files,
                       $conf['mediadir'],
                       'search_media',
                       array('depth'=>$depth),
                       $dir);
 //               $data['_single'] = false;
            }
        }

        // done, yet?
        $len = count($files);
        if(!$len) return $files;
        if($len == 1) return $files;
        // filter images
        for($i=0; $i<$len; $i++){
						$fname = $files[$i]['file'];
						$files[$i]['size'] = 'normal';			// Use configuration size
						if (preg_match('/\_([a-z]+?)\_\.(jpe?g|gif|png)$/',$fname,$matches)){
							$modifier = $matches[1];
							if($modifier == 'pano')						// Is a panoramic image
								$files[$i]['size'] = 'panorama';
							elseif($modifier == 'fullsize')		// Show in full size
								$files[$i]['size'] = 'full';
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
									if(!preg_match($data['filter'],noNS($files[$i]['id']))) unset($files[$i]); // NOM da verificare unset come sopra
							}
            }
            // if(!$files[$i]['isimg']){
// //               unset($files[$i]); // this is faster, because RE was done before
								// array_splice($files, $i, 1); // unset will not reindex the array, so putting the poster on first position fails
								// $len--;
								// $i--;
            // }elseif($data['filter']){
                // if(!preg_match($data['filter'],noNS($files[$i]['id']))) unset($files[$i]); // NOM da verificare unset come sopra
            // }
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

        // limits and offsets?
        if($data['offset']) $files = array_slice($files,$data['offset']);
        if($data['limit']) $files = array_slice($files,0,$data['limit']);

				// puts poster element in first array position
				$i = array_search($data['posterimg'], array_column($files, 'file'));
				if ($i != 0){
					$tmp = $files[0];
					$files[0] = $files[$i];
					$files[$i] = $tmp;
				}
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
				$R->doc .= '<img src="'.DOKU_PHOTOGALLERY.'images/zoom.png">';
				$R->doc .= '</div>'.DOKU_LF;
        $R->doc .= '</a>'.DOKU_LF;

				if ($cmd == 'show'){
					$R->doc .= $this->_lightgallery($files,$data,$pgid);

					// Create lightGallery init function
					$ch = strval(intval($data['th'])+20);
					$R->doc .= '<script>'.DOKU_LF;
					$R->doc .= 'function InitPgGallery(){'.DOKU_LF;
					$R->doc .= 'jQuery("ul.pg-show").lightGallery({'.DOKU_LF;
					$R->doc .= 'thumbnail:true,'.DOKU_LF;
					$R->doc .= 'autoplay:true,'.DOKU_LF;
					$R->doc .= 'showAfterLoad:true,'.DOKU_LF;
					$R->doc .= 'pause:4000,'.DOKU_LF;
					$R->doc .= 'preload:1,'.DOKU_LF;
					$R->doc .= 'mode:"lg-fade",'.DOKU_LF;
					$R->doc .= 'thumbWidth:'.$data['tw'].','.DOKU_LF;
					$R->doc .= 'thumbContHeight:'.$ch.DOKU_LF;
					$R->doc .= '});}'.DOKU_LF;
					$R->doc .= '</script>'.DOKU_LF;
					
					// Override styles to match thumb size
					$R->doc .= '<style>.lg-outer.lg-pull-caption-up.lg-thumb-open .lg-sub-html {bottom:'.$ch.'px;}</style>';
				}
				$R->doc .= '</div>'.DOKU_LF;
    }

    /**
     * Defines how a description  should look like
     */
    function _description($files,$data,$R){
				if ($data['posteralign'] == 'right')
					$R->doc .= '<div class="pg-description pg-left">'.DOKU_LF;
				else
					$R->doc .= '<div class="pg-description pg-right">'.DOKU_LF;
				
				$R->header($data['title'],2,0);
				$R->doc .= '<p>'.$data['description'].'</p>';
				$R->doc .= '<p>';
				$R->doc .= sprintf($this->getLang('imagescnt'),count($files));
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

        // prepare thumbnail dimensions
				$tw = $data['tw'];
				$th = $data['th'];
				$tdim = array('w'=>$data['tw'],'h'=>$data['th']);
        $tsrc  = ml($img['id'],$tdim);
				// and attributes
        $ta = array();
        $ta['alt'] = $this->_caption($img,$data);
        $tatt = buildAttributes($ta);

        // prepare image dimensions
				if ($img['size'] == 'panorama'){
						$cropw = 4000; //NOM mettere parametri di config
						$croph = $data['ih'];
				} elseif ($img['size'] == 'full'){
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
        $isrc = ml($img['id'],$idim);
        //prepare image attributes
        // $ia  = array();
        // $ia['width'] = $iw;
        // $ia['height'] = $ih;
        // $ia['border'] = 0;
        // $ia['title'] = $this->_caption($img,$data);
        // $iatt = buildAttributes($ia); //NOM not used yet

  			$ret ='';
				$style =' style="display:none;"';
				$style = ''; //NOM: controllare
				// override for videos
				$video = '';
				if($img['isvid']){
						$video .= '<div id="video'.$idx.'" style="display:none;">'.DOKU_LF;
						$video .= '<video class="lg-video-object lg-html5" controls preload="none">';
						$video .= '<source src="'.$isrc.'" type="video/mp4">';
						$video .= 'Your browser does not support HTML5 video.';
						$video .= '</video>'.DOKU_LF;
						$video .= '</div>'.DOKU_LF;
						if($img['poster']){
								$isrc = ml($img['poster'],$idim);
								$tsrc = ml($img['poster'],$tdim);
						}else{
								$isrc = DOKU_PHOTOGALLERY.'images/movie_poster.jpg';
								$tsrc = DOKU_PHOTOGALLERY.'images/movie_thumb.png';
						}
						$ret .= '<li data-poster="'.$isrc.'" data-sub-html="video caption1" data-html="#video'.$idx.'">'.DOKU_LF;
				}else{
						$ret .= '<li data-src="'.$isrc.'"'.$style.'>'.DOKU_LF;
				}
				if ($idx < 25){
					$ret .= '<img class="pg-preload" src="'.$tsrc.'" '.$tatt.'/>'.DOKU_LF;
				}
				else{
					$ret .= '<img class="pg-preload" data-src="'.$tsrc.'" '.$tatt.'/>'.DOKU_LF;
				};
				if ($idx < 4){
					$ret .= '<img class="pg-preload" style="display:none;" src="'.$tsrc.'"/>'.DOKU_LF;
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
						$this->_addString($ret,$ret,NULL,NULL,'<p>','</p>');
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
}
  
	    // function _showname($img,$data){
        // global $ID;

        // if(!$data['showname'] ) { return ''; }

        // //prepare link
        // $lnk = ml($img['id'],array('id'=>$ID),false);

        // // prepare output
        // $ret  = '';
        // $ret .= '<br /><a href="'.$lnk.'">';
        // $ret .= hsc($img['file']);
        // $ret .= '</a>';
        // return $ret;
    // }

     // //prepare link
        // $lnk = ml($img['id'],array('id'=>$ID),false);

        // // prepare output
        // $ret  = '';
        // $ret .= '<br /><a href="'.$lnk.'">';
        // $ret .= hsc($this->_meta($img,'title'));
        // $ret .= '</a>';
        // return $ret;
		