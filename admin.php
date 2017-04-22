<?php
/**
 * DokuWiki Plugin photogallery (Admin Component)
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Marco Nolletti
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once('inc/pgdefines.php');

class admin_plugin_photogallery extends DokuWiki_Admin_Plugin {

    /**
     * @return int sort number in admin menu
     */
    public function getMenuSort() {
        return 1000;
    }

    /**
     * @return bool true if only access for superuser, false is for superusers and moderators
     */
    public function forAdminOnly() {
        return false;
    }

    /**
     * Should carry out any processing required by the plugin.
     */
    public function handle() {
    }

    /**
     * Render HTML output, e.g. helpful text and a form
     */
    public function html() {
        ptln('<h1>'.$this->getLang('menu').'</h1>');
				ptln('<div class="table-responsive">');
				ptln('<table class="inline table table-striped table-condensed">');
				$this->_info_row('Plugin info','Value',' ',true);
				$info = $this->getInfo();
				$this->_info_row('Plugin version',$info['date']);
				$this->_info_row('Author',$info['author']);
				$this->_info_row('Server parameters','Value','Status',true);
				$this->_info_row('Plugin folder',__DIR__);
				$ok = version_compare(PHP_VERSION,'5.4.45',">=");
				$this->_info_row('Current PHP version',phpversion(),$ok);
				$this->_info_row('Running webserver',htmlentities($_SERVER['SERVER_SOFTWARE']));
				$this->_info_row('PHP memory limit',ini_get('memory_limit'));
				$ok = extension_loaded('exif');
				$this->_info_row('EXIF extension',($ok ? '' : 'not').' loaded',$ok);
				$ok = extension_loaded('curl');
				$this->_info_row('CURL extension',($ok ? '' : 'not').' loaded',$ok);
				$ok = extension_loaded('exif');
				$this->_info_row('IMAGICK extension',($ok ? '' : 'not').' loaded',$ok);
				$ok = extension_loaded('zip');
				$this->_info_row('ZIP extension',($ok ? '' : 'not').' loaded',$ok);
				$ok = extension_loaded('gd');
				$this->_info_row('GD extension',($ok ? '' : 'not').' loaded',$ok);
				if($ok){
						$info = gd_info();
						foreach($info as $key => $value) {
								$this->_info_row('|-- '.$key,$value);
						}
				}
				$this->_info_row('phpThumb requirements','Value','Status',true);
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
				$this->_info_row('Important disabled functions',$info,$ok);
				$info = fileperms(PHOTOGALLERY_PGFETCH_FILE) & 0xFFF;
				$ok = (($info & PHOTOGALLERY_PGFETCH_EXE_PERM) == PHOTOGALLERY_PGFETCH_EXE_PERM);
				$this->_info_row('pgImg.php execute permissions',sprintf('%o',$info),$ok);
				ptln('</table>');
				ptln('</div>');
    }

		function _info_row($item, $value, $state = null, $header = false){
				if ($header)
						ptln('<thead>');
				ptln('<tr>');
				$this->_info_cell($item,$header);
				$this->_info_cell($value,$header);
				if(is_bool($state))
						$this->_info_cell($state ? "ok" : "error",$header);
				else
						$this->_info_cell($state,$header);
				ptln('</tr>');
				if ($header)
						ptln('</thead>');
		}
		
		function _info_cell($text, $header = false){
				if ($header)
						ptln('<th>');
				else
						ptln('<td>');
				if ($text != '')
					ptln($text);
				else
					ptln(' ');
				if ($header)
						ptln('</th>');
				else
						ptln('</td>');
		}
}
