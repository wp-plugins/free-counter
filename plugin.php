<?php
    ob_start();
    /**
    * Plugin Name: Free-Counter!!!!
    * Plugin URI: www.free-counter.org
    * Description: <a href="http://www.free-counter.org/">Counter and statistics</a> plugin and Widget for WordPress.
    * Author: Free counter
    * Version: 1.2
    * Author URI: http://www.free-counter.org/
    */

    define("SERVER_URL", "www.free-counter.org");
    define("SERVER_URL_VISIT", "http://www.free-counter.org/");
    define("_PREFIX", "counter_free_");   
    define("IMG", "http://www.free-counter.org/images/");   
    define("PAGES_NEXT_PREV_COUNT", 3);
    class counter_free_plagin {

        // data result 
        static private $result = "";

        // counter data
        static private $data_counter = array(); 
        // file name for cache
        static private $file_hash = "";
        //flag activate
        static private $activate = true;

        /**
        * @method sendToServer - this method sending to server the data 
        * 
        */
        static public function sendToServer($postdata = array())
        {

            if (count($postdata) > 0) {
                if (isset(self::$data_counter['counter_id'])) {
                    $postdata['counter_id'] = self::$data_counter['counter_id'];
                } 
                if ($counter_id = get_option(_PREFIX . 'counter_id')) {
                    $postdata['counter_id'] = $counter_id;
                }
                $postdata = http_build_query($postdata, '', '&');

                $length = strlen($postdata);           

                if (function_exists("fsockopen")) {
                    $out = "POST /Api.php HTTP/1.1\r\n";
                    $out.= "HOST: " . SERVER_URL . "\r\n";
                    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
                    $out.= "Content-Length: ".$length."\r\n";
                    $out.= "Connection:Close\r\n\r\n";
                    $out.= $postdata."\r\n\r\n";
                    try {
                        $errno='';
                        $errstr = '';
                        $socket = @fsockopen(SERVER_URL, 80, $errno, $errstr, 30);
                        if($socket){
                            if(!fwrite($socket, $out)){
                                throw new Exception("unable to write");
                            } else {
                                while ($in = @fgets ($socket, 1024)){
                                    self::$result .= $in;
                                } 

                            }
                            self::$result = explode("\r\n\r\n", self::$result);
                            preg_match("/(a:|s:|i:).*/", self::$result[1], $res);
                            if (isset($res[0])) {
                                return unserialize($res[0]);
                            }
                            throw new Exception("error in data");
                        } else {
                            throw new Exception("unable to create socket");
                        }
                        fclose($socket);
                    } catch(exception $e) {
                        return false;
                    }
                } elseif (function_exists("curl_init") && function_exists("curl_setopt") && function_exists("curl_exec") && function_exists("curl_close")) {
                    $curl = curl_init(SERVER_URL_VISIT . "/Api.php");
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
                    self::$result = curl_exec($curl);
                    curl_close($curl);
                    preg_match("/(a:|s:|i:).*/", self::$result, $res);
                    if (isset($res[0])) {
                        return unserialize($res[0]);
                    }
                } 
            }
        }
        /**
        * @method on_activate - this method to adding the default option for the widget and plugin
        * 
        */
        static function on_activate()
        {
            // check localhost or local network
            if (self::check_site()) {
                $data_post = array("action" => "create_new_counter", "site_url" => get_option('siteurl'));
                if ($result = self::sendToServer($data_post)) {  
                    if (isset($result['status']) && $result['status'] == "ok" && 
                    isset($result['default_image']) && isset($result['default_hidden']) && 
                    isset($result['code']) && isset($result['images'])) {
                        add_option(_PREFIX . 'counter_id', $result['counter_id'], '', 'yes');  // key for counter
                        add_option(_PREFIX . 'default_image', $result['default_image'], '', 'yes');  
                        add_option(_PREFIX . 'default_hidden', $result['default_hidden'], '', 'yes');
                        add_option(_PREFIX . 'counter_code', $result['code'], '', 'yes');
                        add_option(_PREFIX . 'image_color', $result['image_color'], '', 'yes');
                        add_option(_PREFIX . 'images', $result['images'], '', 'yes');
                        add_option(_PREFIX . 'email', $result['email'], '', 'yes');
                        add_option(_PREFIX . 'password', $result['password'], '', 'yes');   // autoload config
                        
                        self::$data_counter['images'] = $result['images'];
                        self::$data_counter['counter_id'] = $result['counter_id'];
                    }
                } 
            }
        }

        private static function getWeekDates() 
        {
            $week_day = date("w");
            $week_day = ($week_day == 0) ? 7 : $week_day;
            $day_now = date("j");
            $end_week_day = $day_now + (7 - $week_day);
            $start_week_day = $day_now - ($week_day - 1);
            $day_in_month = date("t");
            $day_in_month_pref = date("t", mktime(0,0,0, date("n")-1));
            $now_month = date("n");
            $change_month = "";
            if($end_week_day > $day_in_month) {
                $day_next_month = $end_week_day - $day_in_month  ;
                $end_week_day = $day_next_month;
                $change_month = $now_month + 1;
            } 
            if($start_week_day < 1) {
                $day_next_month = $day_in_month_pref + $start_week_day; // т.к. число  $start_week_day отрецательное
                $start_week_day = $day_next_month; 
                $change_month = $now_month - 1;
            }
            $year_change  = $year = 0;

            if ($change_month != "" && $change_month < 1) {   
                $year_change = date("Y") - 1;   
                $change_month = 12;
            } elseif ($change_month != "" && $change_month > 12) {
                $year_change = date("Y") + 1;
                $change_month = 1;
            } 

            $year = date("Y");

            if ($change_month != "" && $change_month > $now_month) {       
                if (!empty($year_change) && $year_change > $year) {
                    $dates_start_week = mktime(0,0,0, $now_month, $start_week_day, $year);
                    $date_end_week = mktime(23, 59,59, $change_month, $end_week_day, $year_change);
                } elseif (!empty($year_change) && $year_change < $year) {
                    $dates_start_week = mktime(0,0,0, $change_month , $start_week_day, $year_change);
                    $date_end_week = mktime(23, 59,59, $now_month, $end_week_day, $year);
                } else {                            
                    $dates_start_week = mktime(0,0,0, $now_month, $start_week_day, $year);
                    $date_end_week = mktime(23, 59,59, $change_month, $end_week_day, $year);
                }

            } elseif ($change_month != "" && $change_month < $now_month) { 
                if (!empty($year_change) && $year_change > $year) {
                    $dates_start_week = mktime(0,0,0, $now_month, $start_week_day, $year);
                    $date_end_week = mktime(23, 59, 59, $change_month, $end_week_day, $year_change);
                } elseif (!empty($year_change) && $year_change < $year) {
                    $dates_start_week = mktime(0,0,0, $change_month, $start_week_day, $year_change);
                    $date_end_week = mktime(23, 59, 59, $now_month, $end_week_day, $year);
                } else {
                    $dates_start_week = mktime(0,0,0, $change_month, $start_week_day, $year);
                    $date_end_week = mktime(23, 59, 59, $now_month, $end_week_day, $year);
                }
            } else {
                if (!empty($year_change) && $year_change > $year) {
                    $dates_start_week = mktime(0,0,0, $now_month, $start_week_day, $year);
                    $date_end_week = mktime(23, 59,59, $now_month, $end_week_day, $year);
                } elseif (!empty($year_change) && $year_change < $year) {
                    $dates_start_week = mktime(0,0,0, $now_month, $start_week_day, $year);
                    $date_end_week = mktime(23, 59,59, $now_month, $end_week_day, $year);
                }
                $dates_start_week = mktime(0,0,0, $now_month, $start_week_day, $year);
                $date_end_week = mktime(23, 59,59, $now_month, $end_week_day, $year);
            }
            $array_dates['start_week_time'] = $dates_start_week;
            $array_dates['end_week_time'] = $date_end_week;
            return $array_dates;
        }

        /**
        * @method on_deactivate - this method delete option in the widget and plugin
        */
        static function on_deactivate()
        {
            delete_option(_PREFIX . 'counter_id');
            delete_option(_PREFIX . 'images');
            delete_option(_PREFIX . 'default_image');
            delete_option(_PREFIX . 'default_hidden');
            delete_option(_PREFIX . 'counter_code');
            delete_option(_PREFIX . 'image_color');
            delete_option(_PREFIX . 'email');
            delete_option(_PREFIX . 'password');
            delete_option(_PREFIX . 'activate');
        }

        /**
        * @method draw_menu - adds a menu item
        * 
        */
        static function draw_menu()
        {
            
            // position in admin menu
            if (!is_dir(plugin_dir_path( __FILE__ ) . "temp")) {
                mkdir(plugin_dir_path( __FILE__ ) . "temp");
            }
            if(self::check_site()) {
                
                $menu_position = '26.1234567891';
                add_menu_page(
                'Statistic for Counter', 
                'Counter Statistic', 
                'manage_options', 
                'counter_free_plagin', 
                array('counter_free_plagin', 'stat_view'),
                plugins_url('/free-counter.org_icon.png', __FILE__),
                $menu_position
                );
            }
        }

        /**
        * @method save_account - Registration for the hide password statistic and great opportunities
        * 
        */
        static function save_account()
        {
            if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['password_repeat'])) {

                $image_default = get_option(_PREFIX . 'default_image');
                $image_hidden = get_option(_PREFIX . 'default_hidden');
                $image_color = get_option(_PREFIX . 'image_color');

                $email = $_POST['email'];
                if ($_POST['password'] == $_POST['password_repeat']) {
                    $password = trim($_POST['password']);
                } 
                $act = false;
                if (empty($email)) {
                    $act = true;
                } elseif(isset($password) && empty($password)) {
                    $act = true;
                } elseif(!preg_match("/^([a-z0-9_\-]+\.)*[a-z0-9_\-]+@([a-z0-9][a-z0-9\-]*[a-z0-9]\.)+[a-z]{2,4}$/i", $email)) {
                    $act = true;
                }
                if (!$act) {
                    $data_post_default = array(
                    "action" => "get_count_image_on_setting", 
                    "image" => $image_default, 
                    "hidden" => $image_hidden, 
                    "image_color" => $image_color,
                    "email" => $email,
                    "password" => $password);
                    $result_default_image_setting = counter_free_plagin::sendToServer($data_post_default);
                    if (isset($result_default_image_setting['code'])) {
                        add_option(_PREFIX . 'email', $result_default_image_setting['email']);
                        update_option(_PREFIX . 'email', $result_default_image_setting['email']); 
                        add_option(_PREFIX . 'password', $result_default_image_setting['password']);
                        update_option(_PREFIX . 'password', $result_default_image_setting['password']);
                    }
                }
                header("location: " . admin_url( 'admin.php?page=counter_free_plagin' ));
            }
        }
        /**
        * @method stat_view - view Statistics
        * 
        */
        static function stat_view() 
        {

            $data = array('action' => 'get_statistic');
            // hash
            self::$file_hash = plugin_dir_path( __FILE__ ) . "/temp/data";
            $data_hash = self::getHash();
            $data['count_hash'] = count($data_hash);
            $data['time_hash'] = filemtime(self::$file_hash);
            if ($data['count_hash'] == 0) {
                $data['first'] = true;
            }          
            //end hash  
            $email = get_option(_PREFIX . 'email');
            $password = get_option(_PREFIX . 'password'); 
            $month_now = date("n");
            $year_now = date("Y");
            $start_today = date("j");
            $id_counter = get_option(_PREFIX . 'counter_id');
            $image_default = get_option(_PREFIX . 'default_image');
            $image_hidden = get_option(_PREFIX . 'default_hidden');
            $date_week = self::getWeekDates();
            if ($res = self::sendToServer($data)) { // $res = self::sendToServer($data) 

                if (isset($res["stat"]['all']['months'])) {
                    $browser = isset($res["stat"]['all']['details']['browser']) ? self::sort_data($res["stat"]['all']['details']['browser']) : array();
                    $os = isset($res["stat"]['all']['details']['os']) ? self::sort_data($res["stat"]['all']['details']['os']) : array();
                    $data_screen = isset($res["stat"]['all']['details']['data_screen']) ? self::sort_data($res["stat"]['all']['details']['data_screen']) : array();
                    $data_bit = isset($res["stat"]['all']['details']['data_bit']) ? self::sort_data($res["stat"]['all']['details']['data_bit']) : array();
                    $data_countries = isset($res["stat"]['all']['details']['countries']) ? self::sort_data($res["stat"]['all']['details']['countries']) : array(); 
                    $data_city = isset($res["stat"]['all']['details']['cities']) ? self::sort_data($res["stat"]['all']['details']['cities']) : array();
                }

                if (isset($res['stat']['record']['data']) && isset($res['stat']['count_data'])) {   
                    $data_hash = checkArray($res['stat']['record']['data'], $data_hash, $res['stat']['record']['visitors_count']);
                    $data['count_hash'] = count($data_hash);
                    if ($data['count_hash'] > $res['stat']['count_data']) {
                        $temp = array_chunk($data_hash, $res['stat']['count_data']);
                        $data_hash = $temp[0];
                    }
                    self::saveHash($data_hash);
                }
            }
            $record = self::getHash();
            $stat_chart_day = (isset($res["stat"]['all']["all_month"]) ? $res["stat"]['all']["all_month"] : array());
            $stat_chart_month = (isset($res["stat"]['all']["months"]) ? $res["stat"]['all']["months"] : array());
            $stat_chart_week = (isset($res["stat"]['week_days_stat']) ? $res["stat"]['week_days_stat'] : array());
            ob_start();
            include(plugin_dir_path( __FILE__ ) . "template/statistic.php");
            $form = ob_get_clean();
            echo $form;
        }
        /**
        * @method exportToCsv - export data to a CSV file
        * 
        */
        static function exportToCsv() 
        {

            $filename = "export.csv";
            self::$file_hash = plugin_dir_path( __FILE__ ) . "temp/data";
            $data_hash = self::getHash(); 

            $data_keys = array(
            'date', 'ip', 'country', 'city',
            'system', 'browser', 'screen resolution' ,
            'screen color', 'javascript', 
            'query', 'referrer',
            );
            $data = array();
            $data[0] = $data_keys;
            $i = 1;
            foreach($data_hash as $k => $val) {
                $data[$i][1] = $val['4'];
                $data[$i][2] = $val['3']['ip'];
                $data[$i][3] = $val['3']['country'];
                $data[$i][4] = $val['3']['city'];
                $data[$i][5] = $val[6]['operating_systems'];
                $data[$i][6] = $val['5']['name'];
                $data[$i][7] = $val['1'];
                $data[$i][8] = $val['2'] . " Bit";
                $data[$i][9] = $val['0'];
                $data[$i][10] = $val['8'];
                $data[$i][11] = $val['9'];
                $i++;
                if ($i == 200) {
                    break;
                }
            }
            self::download_send_headers($filename);
            echo self::array2csv($data);
            exit;
        }
        /**
        * @method - setting the headers for the export file
        * 
        * @param string $filename 
        */
        static function  download_send_headers($filename)
        {
            // disable caching
            $now = gmdate("D, d M Y H:i:s");
            header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
            header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
            header("Last-Modified: {$now} GMT");
            // force download  
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            // disposition / encoding on response body
            header("Content-Disposition: attachment;filename={$filename}");
            header("Content-Transfer-Encoding: binary");
        }
        /**
        * @method array2csv - conversion to CSV data
        * 
        * @param array $array
        * @return string
        */
        static function array2csv(array &$array)
        {
            if (count($array) == 0) {
                return null;
            }
            ob_start();
            $df = fopen("php://output", 'w');
            foreach ($array as $row) {
                fputcsv($df, $row);
            }
            fclose($df);
            return ob_get_clean();
        }

        /**
        * save cache data
        * 
        * @param mixed $data
        */
        static function saveHash($data = array())
        {
            file_put_contents(self::$file_hash, serialize($data));
        }
        /**
        * get cached data
        * 
        */
        static function getHash() 
        {
            $data = array();
            if (file_exists(self::$file_hash)) {
                $contant = file_get_contents(self::$file_hash);
                $data = unserialize($contant);
            } else {
                self::saveHash();
            }
            return $data;
        }

        static function get_pages($page, $pages)
        {
            echo '<div id="page_item">Page Item: <select  onchange="setCountShowVisitorsOnDays(this)">';
            $count_item_in_pages = array(15, 30, 50);
            for($i=0, $n = count($count_item_in_pages); $i < $n; $i++) {
                $sel = "";
                if (isset($_SESSION['selected']) && $_SESSION['selected'] == $count_item_in_pages[$i]) {
                    $sel = 'selected = "selected"'; 
                }
                echo '<option value="' . $count_item_in_pages[$i] . '" ' . $sel . ' >' . $count_item_in_pages[$i] . '</option>';
            }
            echo '</select></div>&nbsp;&nbsp;';
            if ($page != 1) {
                $prev = $page - 1;
                $url_prev = plugins_url("admin.php?page=counter_free_plagin&p={$prev}#header-visitors-days", __FILE__);
                echo "<a href=\"$url_prev\">Prev Page</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

            }
            $prev_pages_visible = $page - PAGES_NEXT_PREV_COUNT;
            if ($prev_pages_visible > 0) {
                $url_first = admin_url("admin.php?page=counter_free_plagin&p=1#header-visitors-days", __FILE__);
                echo "<a href=\"$url_first\">1</a>&nbsp;&nbsp;...&nbsp;&nbsp;";
            }
            if (($page - PAGES_NEXT_PREV_COUNT) > 0) {
                $prev_pages_visible = $page - PAGES_NEXT_PREV_COUNT;
            } elseif (($page - PAGES_NEXT_PREV_COUNT) == 0) {
                $prev_pages_visible = 1 ;
            } else {
                $prev_pages_visible = PAGES_NEXT_PREV_COUNT - $page;
            }
            if ($prev_pages_visible >= 1) {
                for($i = $prev_pages_visible;  $i <= ($page-1) ; $i++) {
                    $url_i_prev = admin_url("admin.php?page=counter_free_plagin&p=$i#header-visitors-days", __FILE__);
                    echo "<a href=\"$url_first\">$i</a>&nbsp;&nbsp;";
                }
            }
            echo "$page &nbsp;&nbsp;"; 
            if (($page + PAGES_NEXT_PREV_COUNT) < $pages) {
                $next_pages_visible = $page + PAGES_NEXT_PREV_COUNT;
            } else {
                $next_pages_visible = $pages;
            }
            if ($next_pages_visible <= $pages) {
                for($i = ($page + 1); $i <= $next_pages_visible; $i++) {
                    $url_i_next = admin_url("admin.php?page=counter_free_plagin&p=$i#header-visitors-days", __FILE__);
                    echo "<a href=\"$url_i_next\">$i</a>&nbsp;&nbsp;";
                }
            }
            $next_pages_visible = $page + PAGES_NEXT_PREV_COUNT;
            if ($next_pages_visible < $pages) {
                $url_last = admin_url("admin.php?page=counter_free_plagin&p=$pages#header-visitors-days", __FILE__);
                echo "...&nbsp;&nbsp;<a href=\"$url_last\">". $pages ." </a>";
            }
            if ($page != $pages) {
                $next = $page + 1;
                $url_next = admin_url("admin.php?page=counter_free_plagin&p=$next#header-visitors-days", __FILE__);
                echo "&nbsp;&nbsp;&nbsp;<a href=\"$url_next\">Next Page</a>&nbsp;&nbsp;";
            }
        }

        /**
        * sorting input data(array)
        * 
        * @param array $data
        * @return array()
        */
        static function sort_data($data = array())
        {
            $max = 0;
            $record_data = array();
            $is_array = false;
            foreach($data as $data_id => $data_value) {
                if (is_array($data_value)) {
                    $max = $max + $data_value['count'];
                    $record_data[] = $data_value['count']; 
                    $is_array = true;
                } else {
                    $max = $max + $data_value;
                    $record_data[] = $data_value;
                }
            }    
            if ($is_array) {
                array_multisort($record_data, SORT_DESC, SORT_NUMERIC, $data);
            } else {
                arsort($data);
            }

            return array("max" => $max, "data" => $data);
        }

        /**
        * @method widgets_initial - initialize widget
        * 
        */
        static function widgets_initial() 
        {
            if (get_option(_PREFIX . 'images')) {
                register_widget('counter_free_widget');
            }
        }
       
        /**
        * check site (local or net local)
        */
        private static function check_site()
        {
            $siteUrl = get_option('siteurl');
            $active = get_option(_PREFIX . 'activate');
            if($active === false || $active == 0) {
                if($siteUrl) {
                    if(strpos($_SERVER['HTTP_HOST'], 'localhost') === false && $_SERVER['REMOTE_ADDR'] != '127.0.0.1' 
                    && !preg_match("/192\.168\.[0-9]{1,3}\.[0-9]{1,3}/i", $_SERVER['REMOTE_ADDR']) 
                    && !preg_match("/172\.(16|17|18|19|20|21|22|23|24|25|26|27|28|29|30|31)\.[0-9]{1,3}\.[0-9]{1,3}/i", $_SERVER['REMOTE_ADDR']) 
                    && !preg_match("/10.\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/i", $_SERVER['REMOTE_ADDR'])) {
                        $p_url = parse_url($siteUrl);
                        if ($p_url['host'] != $_SERVER['HTTP_HOST']) {
                            return false;
                        } else {
                            if(isset($p_url['path']) && strpos($_SERVER['REQUEST_URI'], $p_url['path']) === false) {
                                return true;
                            }
                        }
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
            return true;
        }

        /**
        * @method notices - Check the settings for Free-counter
        * 
        */
        public static function notices()
        {
            $siteUrl = get_option('siteurl');
            $active = get_option(_PREFIX . 'activate');
            if($active === false || $active == 0) {
                if($siteUrl) {
                    if(strpos($_SERVER['HTTP_HOST'], 'localhost') === false && $_SERVER['REMOTE_ADDR'] != '127.0.0.1' 
                    && !preg_match("/192\.168\.[0-9]{1,3}\.[0-9]{1,3}/i", $_SERVER['REMOTE_ADDR']) 
                    && !preg_match("/172\.(16|17|18|19|20|21|22|23|24|25|26|27|28|29|30|31)\.[0-9]{1,3}\.[0-9]{1,3}/i", $_SERVER['REMOTE_ADDR'])
                    && !preg_match("/10.\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/i", $_SERVER['REMOTE_ADDR'])) {
                        $p_url = parse_url($siteUrl);
                        if ($p_url['host'] != $_SERVER['HTTP_HOST']) {
                            self::noticesMsg('Check the WordPress settings, <strong>WordPress URL</strong> and <strong>Physical Web Page URL</strong>of your Home Page - must be equal.');
                            return false;
                        } else {
                            if(isset($p_url['path']) && strpos($_SERVER['REQUEST_URI'], $p_url['path']) === false) {
                                self::noticesMsg('Check the WordPress settings, <strong>WordPress URL</strong> or Home Path URL (E.g. http://www.domain.com/[home_path]). Plugin may not work properly.');
                                return false;
                            }
                        }
                    } else {
                        self::noticesMsg('We are sorry, but we do not serve "localhost" or Local Networks.');
                        return false;
                    }
                } else {
                    self::noticesMsg('Check your URL in WordPress settings for your Web Page.');
                    return false;
                }
            }
            return true;
        }

        /**
        * @method noticesMsg - output message to admin panel 
        * 
        * @param string $msg
        */
        private static function noticesMsg($msg = "")
        {

            if (!empty($msg)) {
                echo '<div class="error" style="text-align: center;">
                <span style="color: red; font-size: 14px; font-weight: bold; margin-top:3px;">Attention!!!</span><br />
                <span> Plugin FREE-COUNTER: ' . $msg . ' Anyway <a href="javascript:activate_free_counter()"><strong>activate</strong></a>.</span><br />
                <span>If you have any requests or suggestions, please <a href="' . SERVER_URL_VISIT . 'contact" target="_blank">contact us</a>.</span>
                <form action="' . admin_url( 'admin-post.php' ) . '" method="post" name="form_activate_free_counter" id="form_activate_free_counter">
                <input type="hidden" name="action" value="activate_plugin" >
                </form>
                <script>
                function activate_free_counter()
                {
                document.form_activate_free_counter.submit();
                }
                </script>
                </div>';
            }

        }
        /**
        * activate local plugin
        * 
        */
        public static function activate_plugin()
        {
            add_option(_PREFIX . 'activate', 1, '', true);
            self::on_activate();
            header("location: " . admin_url( 'admin.php?page=counter_free_plagin' ));
        }
    }

    class counter_free_widget extends WP_Widget {

        /**
        * create default params for form
        * 
        */
        function __construct()
        {
            $widget_ops = array( 'classname' => 'counter_free_widget', 'description' => 'Counter Free for your site' );

            $control_ops = array( 'width' => 400, 'height' => 550, 'id_base' => 'counter_free_widget' );
            if (version_compare(phpversion(), '5.0.0', '>=')) {
                parent::__construct('counter_free_widget', 'Free Counter', $widget_ops, $control_ops);
            } else {
                $this->WP_Widget('counter_free_widget', 'Free Counter', $widget_ops, $control_ops );
            }
        }

        /**
        *  this is method shows the counter in sidebar
        * 
        * @param array $args - default params for shows widget
        * @param array $instance - widget params
        */
        function widget( $args, $instance ) 
        {
            $code = get_option(_PREFIX . 'counter_code');
            echo $code;
        }

        /**
        * shows form in widget page
        * 
        * @param array $instance - widget params
        */
        function form( $instance ) 
        {

            $images = get_option(_PREFIX . 'images');
            $image_default = get_option(_PREFIX . 'default_image');
            $image_hidden = get_option(_PREFIX . 'default_hidden');
            $image_color = get_option(_PREFIX . 'image_color');

            $defaults = array("images" => $images, "default_image" => $image_default, "default_hidden" => $image_hidden, "color_image" => $image_color);
            $instance = wp_parse_args($defaults, $instance  ); 
            ob_start();
            include(plugin_dir_path( __FILE__ ) . "template/settings_form.php");
            $form = ob_get_clean();
            echo $form;

        }

        /**
        * Update option for plugin
        * 
        * @param array $new_instance
        * @param array $old_instance
        */
        function update( $new_instance, $old_instance ) 
        {
            if (isset($new_instance['counter_type']) && isset($new_instance['counter_hidden']) && isset($new_instance['counter_image_color'])) {   
                update_option(_PREFIX . 'default_image', $new_instance['counter_type']);
                update_option(_PREFIX . 'default_hidden', $new_instance['counter_hidden']);
                update_option(_PREFIX . 'image_color', $new_instance['counter_image_color']);
                $email = "";
                $password = "";
                $data_post_default = array(
                "action" => "get_count_image_on_setting", 
                "image" => $new_instance['counter_type'], 
                "hidden" => $new_instance['counter_hidden'], 
                "image_color" => $new_instance['counter_image_color'],
                "email" => $email,
                "password" => $password);
                $result_default_image_setting = counter_free_plagin::sendToServer($data_post_default);
                if (isset($result_default_image_setting['code'])) {
                    update_option(_PREFIX . 'counter_code', $result_default_image_setting['code']);
                }
            }
        }
    } 
    if(is_admin()) {
        // check server - local or local network
        add_action('admin_notices', array('counter_free_plagin', 'notices'));
        // activate plugin (set default setting)
        register_activation_hook( __FILE__, array('counter_free_plagin','on_activate'));
        // deactive plugin (delete setting)
        register_deactivation_hook( __FILE__, array('counter_free_plagin','on_deactivate'));
        // add item to admin menu
        add_action('admin_menu', array('counter_free_plagin', 'draw_menu'));
        // export to csv
        add_action('admin_post_exportToCsv', array('counter_free_plagin', 'exportToCsv') );
        // save account
        add_action('admin_post_save_account', array('counter_free_plagin', 'save_account') );
        // activate plugn(set default setting)
        add_action('admin_post_activate_plugin', array('counter_free_plagin', 'activate_plugin') );
        // check statistic
        add_action('wp_ajax_nopriv_check_stat', array('counter_free_plagin', 'check_stat') );   
        // add style to admin panel
        add_action('admin_print_styles', "adding_files_style" );   
        // add js to admin panel
        add_action('admin_print_scripts', "adding_files_script" );   
    }   
    // initiate widget   
    add_action('widgets_init', array('counter_free_plagin', 'widgets_initial') );

    /**
    * check hash and server data
    */
    function checkArray($ar1, $ar2, $count_array)
    {
        if (count($ar2) > 0) {
            $unique = array();
            foreach ($ar2 as $elem) {
                $flag = inArray($elem, $ar1);
                if ($flag !== false) {
                    unset($ar1[$flag]);
                } 
                $unique[] = $elem;
            }
            if (count($ar1) > 0) {
                foreach($ar1 as $k => $v) {
                    if (isset($count_array[$ar1[$k][10]])) {
                        $ar1[$k][10] = $count_array[$ar1[$k][10]];
                    }
                }
                $unique = array_merge_recursive($ar1, $ar2);
            }
            return $unique;
        } else {
            foreach ($ar1 as $key =>$elem) {
                if (isset($count_array[$ar1[$key][10]])) {
                    $ar1[$key][10] = $count_array[$ar1[$key][10]];
                }
            }
            return $ar1;
        }
    }
    /**
    * search element in array
    */
    function inArray($element, $array) 
    {
        $f = false;
        $elem_hash = md5($element[4]);
        foreach($array as $i => $value) {
            if ($elem_hash == md5($array[$i][4])) {
                $f = $i;
                break;
            }
        }
        return $f;
    }
    /**
    * load styles and scrits
    * 
    */
    function adding_files_style() {
        wp_register_style('free_counter_css', plugins_url('template/counter_style.css', __FILE__));
        wp_enqueue_style('free_counter_css');
        wp_register_style('free_counter_jquery_minicolors_css', plugins_url("template/jquery.minicolors.css",__FILE__));
        wp_enqueue_style('free_counter_jquery_minicolors_css');


    }
    /**
    *  load javascript to admin panel
    * 
    */
    function adding_files_script()
    {
        wp_register_script('free_counter_script', plugins_url("template/counter.js",__FILE__));
        wp_enqueue_script('free_counter_script');
        wp_register_script('free_counter_jquery_minicolors_js', plugins_url("template/jquery.minicolors.js",__FILE__));
        wp_enqueue_script('free_counter_jquery_minicolors_js');
    }



?>
