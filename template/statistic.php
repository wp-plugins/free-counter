<link rel='stylesheet' href='<?php echo plugins_url("counter_style.css",__FILE__);?>' type='text/css' media='all' />
<script type="text/javascript" src="<?php echo plugins_url("counter.js",__FILE__);?>"></script> 
<div class="stat_block">
    <div class="stat_title">
        <?php echo "Statistic for your Counter"; ?>
    </div>
    <div class="stat_all">
        <table class="table_stat">
            <tbody>
                <tr>
                    <th class="stat">
                    </th>
                    <th class="stat">
                        Today<br>
                        <?php echo date("j.m.y"); ?>
                    </th>
                    <th class="stat">
                        Week<br>
                        <?php echo date("d.m.Y", $date_week['start_week_time']) . ' - ' . date("d.m.Y", $date_week['end_week_time']); ?>
                    </th>
                    <th class="stat">
                        Month<br>
                        <?php echo date("F"); ?>
                    </th>
                    <th class="stat">
                        All
                    </th>
                </tr>
                <tr >
                    <td class="stat">
                        Visitors
                    </td>
                    <td class="stat">
                        <?php
                            echo (isset($res["stat"]['all']["all_month"][$year_now][$month_now][$start_today]) ? $res["stat"]['all']["all_month"][$year_now][$month_now][$start_today] : "0"); ?>
                    </td>
                    <td class="stat">
                        <?php echo (isset($res['stat']['week_stat']) ? $res['stat']['week_stat'] : "0"); ?> 
                    </td>
                    <td class="stat">
                        <?php 
                            if (isset($res["stat"]['all']["all_month"][$year_now][$month_now])) {
                                $count = 0;
                                foreach($res["stat"]['all']["all_month"][$year_now][$month_now] as $val) {
                                    $count += $val;
                                }
                                echo $count;
                            } else {
                                echo 0;
                            }

                        ?> 
                    </td>
                    <td class="stat">
                        <?php echo (isset($res['stat']['all']['years']) ? $res['stat']['all']['years'] : "0"); ?>
                    </td>

                </tr>
            </tbody>
        </table>
    </div>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <?php if (isset($stat_chart_day) && count($stat_chart_day) > 0) { ?>
        <script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                ['Date', 'Visitor'],
                <?php 
                    $count_date = 1;
                    $c = count($stat_chart_day);
                    $count_date = ceil($c/15)+1;
                    foreach ($stat_chart_day as $k => $v) {
                        foreach($v as $m => $days) {
                            foreach($days as $day => $count){
                                echo "['$day.$m.$k', $count],";
                            }
                        }
                }?>
                ]);
                count_in_stat = '<?php echo $count_date; ?>' ;
                var options = {
                    width: '1200',
                    height:'200',
                    hAxis : {textStyle :{fontSize: 10}, showTextEvery:count_in_stat  },
                    colors:["#00f20e"],
                    title: "Visitors User on Date"
                };

                var chart = new google.visualization.LineChart(document.getElementById('all_stat_chart_day'));
                chart.draw(data, options);
            }
        </script>
        <?php } ?>
    <?php if (isset($stat_chart_month) && count($stat_chart_month) > 0) { ?>
        <script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                ['Date', 'Visitor'],
                <?php 
                    foreach ($stat_chart_month as $month => $days) {
                        echo "['$month', $days],";
                }?>
                ]);

                var options = {
                    width: '400',
                    height:'200',
                    hAxis : {textStyle :{fontSize: 10}, showTextEvery: 1 },
                    colors: ["#00e4f2"],
                    title: "Visitors User on Month"
                };

                var chart = new google.visualization.LineChart(document.getElementById('all_stat_chart_month'));
                chart.draw(data, options);
            }
        </script>
        <?php } ?>
    <?php if (isset($stat_chart_week) && count($stat_chart_week) > 0) { ?>
        <script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                ['Date', 'Visitor'],
                <?php 
                    foreach ($stat_chart_week as $days) {

                        echo "['{$days['date']}', {$days['stat']}],";

                }?>
                ]);

                var options = {
                    width: '650',
                    height:'200',
                    hAxis : {textStyle :{fontSize: 10}, showTextEvery: 1 },
                    colors: ["#00e4f2"],
                    title: "Visitors User on Week"
                };

                var chart = new google.visualization.ColumnChart(document.getElementById('all_stat_chart_week'));
                chart.draw(data, options);
            }
        </script>
        <?php } ?>
    <?php if (isset($browser) && count($browser) > 0) { ?>
        <script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                ['Browser', 'Procent %'],
                <?php 
                    foreach ($browser['data'] as $b) {
                        $procent = round(($b['count']/$browser['max'] )*100, 2);
                        echo "['{$b[0]['name']}', {$procent}],";
                }?>
                ]);

                var options = {
                    width: '250',
                    height:'200',
                    hAxis : {textStyle :{fontSize: 10}, showTextEvery: 1},
                    colors:["#7e00fb"],
                    title: "Browser Brand"
                };

                var chart = new google.visualization.BarChart(document.getElementById('all_stat_chart_browser'));
                chart.draw(data, options);
            }
        </script>
        <?php } ?>
    <?php if (isset($os) && count($os) > 0) { ?>
        <script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                ['OS', 'Procent %'],
                <?php 
                    foreach ($os['data'] as $o) {
                        $procent = round(($o['count']/$os['max'] )*100, 2);
                        echo "['{$o[0]['name']}', {$procent}],";
                }?>
                ]);

                var options = {
                    width: '250',
                    height:'200',
                    hAxis : {textStyle :{fontSize: 10}, showTextEvery: 1},
                    colors:["#fb8004"],
                    title: "OS Brand"
                };

                var chart = new google.visualization.BarChart(document.getElementById('all_stat_chart_os'));
                chart.draw(data, options);
            }
        </script>
        <?php } ?> 
    <?php if (isset($data_screen) && count($data_screen) > 0) { ?>
        <script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                ['Data Screen', 'Procent %'],
                <?php 
                    foreach ($data_screen['data'] as $key => $ds) {
                        $procent = (($ds/$data_screen['max'] )*100);
                        echo "['{$key}', {$procent}],";
                }?>
                ]);

                var options = {
                    width: '250',
                    height:'200',
                    hAxis : {textStyle :{fontSize: 10}, showTextEvery: 1},
                    colors:["#0007ff"],
                    title: "Statistic Data Screen"
                };

                var chart = new google.visualization.BarChart(document.getElementById('all_stat_chart_data_screen'));
                chart.draw(data, options);
            }
        </script>
        <?php } ?>
    <?php if (isset($data_bit) && count($data_bit) > 0) { ?>
        <script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
            google.setOnLoadCallback(drawChart);
            function drawChart() {
                var data = google.visualization.arrayToDataTable([
                ['Data Bit', 'Procent %'],
                <?php 
                    foreach ($data_bit['data'] as $key => $db) {
                        $procent = round(($db/$data_bit['max'] )*100, 2);
                        echo "['{$key} bit', {$procent}],";
                }?>
                ]);

                var options = {
                    width: '250',
                    height:'200',
                    hAxis : {textStyle :{fontSize: 10}, showTextEvery: 1},
                    colors:["#20d853"],
                    title: "Statistic Data Bit"
                };

                var chart = new google.visualization.BarChart(document.getElementById('all_stat_chart_bit'));
                chart.draw(data, options);
            }
        </script>
        <?php } ?>
    <div class="inline">    
        <div id="all_stat_chart_day"></div>
    </div>
    <div class="inline">
        <div id="all_stat_chart_month"></div>
    </div>
    <div class="inline">
        <div id="all_stat_chart_week"></div>
    </div>
    <div class="inline">
        <div id="all_stat_chart_browser"></div>
    </div>
    <div class="inline">
        <div id="all_stat_chart_os"></div>
    </div>
    <div class="inline">
        <div id="all_stat_chart_data_screen"></div>
    </div>
    <div class="inline">
        <div id="all_stat_chart_bit"></div>
    </div>
    <div align="center" style="clear: both;">
        <?php if (isset($data_countries) && isset($data_city) && count($data_city) > 0 && count($data_countries) > 0) { ?>
            <table class="table_stat w-4" cellspacing="1" cellpadding="2" border="0">
                <tr>
                    <th align="center">Statistics by country</th>
                    <th align="center">Statistics by city</th>
                </tr>
                <tr>
                    <td align="center" valign="top"> 
                        <?php 
                            $i = 1;  
                            $count = count($data_countries['data']);
                            foreach ($data_countries['data'] as $country => $val) {

                                $procent = round($val['count']/$data_countries['max']*100, 2) . "%";
                                $code = strtolower($val['code']);
                                if ($i == 1) {
                                    echo "<div id=\"ovt_country\" class=\"moovdiv\"> 
                                    <table class=\"table-stat-moovdiv\">
                                    ";
                                }
                            ?>
                            <tr >
                                <td class="w-2">
                                    <?php echo $country;?>
                                </td>
                                <td class="w-2">
                                    <div class="progress">
                                        <div class="progress_load" style="width: <?php echo  $procent; ?>"></div>
                                    </div>
                                </td>
                                <td class="w-3">
                                    <?php echo $procent; ?>
                                </td>
                            </tr>
                            <?php 
                                if ($i == 10 || $count == $i) {
                                    echo "
                                    </table>
                                    </div>";
                                    break;
                                }
                                $i++;
                        } ?>  

                    </td>
                    <td align="center" valign="top">
                        <?php 
                            $i = 1;  
                            $count = count($data_city['data']);
                            foreach ($data_city['data'] as $city => $val) {

                                $procent = round($val/$data_countries['max']*100, 2) . "%";

                                if ($i == 1) {
                                    echo "<div id=\"ovt_city\" class=\"moovdiv\"> 
                                    <table class=\"table-stat-moovdiv\">
                                    ";
                                }
                            ?>
                            <tr >
                                <td class="w-2">
                                    <?php echo utf8_encode($city);?>
                                </td>
                                <td class="w-2">
                                    <div class="progress">
                                        <div class="progress_load" style="width: <?php echo  $procent; ?>"></div>
                                    </div>
                                </td>
                                <td class="w-3">
                                    <?php echo $procent; ?>
                                </td>
                            </tr>
                            <?php 
                                if ($i == 10 || $count == $i) {
                                    echo "
                                    </table>
                                    </div>";
                                    break;
                                }
                                $i++;
                        } ?>  
                    </td>
                </tr>
            </table>
            <?php } ?>
    </div>
    <?php if (isset($record)) { ?> 
        <div class="block-export">
            <a href="<?php echo admin_url( 'admin-post.php?action=exportToCsv' ) ?>" target="_blank">Export to csv</a> 
        </div>
        <?php } ?>
    <div class="stat_all" id="header-visitors-days">
        <table class="table_stat">
            <tbody>

                <tr>
                    <th class="stat">Date</th>
                    <th class="stat">Ip</th>
                    <th class="stat">System</th>
                    <th class="stat">Browser</th>
                    <th class="stat">Returns</th>
                    <th class="stat">Referer</th>
                </tr>
                <?php if (isset($record)) {
                        foreach($record as $key => $value){ 
                            $hash = md5($value[4].$value[3]['ip']);
                        ?>
                        <tr>
                            <td>
                                <?php echo $value[4] ?>
                            </td>
                            <td>
                                <a href="javascript:void(0)" onclick="openInfo('<?php echo $hash;?>')"><?php echo $value[3]['ip'];?></a>
                            </td>
                            <td class="stat">
                                <img src="<?php echo IMG . $value[6]['img']; ?>" alt="<?php echo($value[6]['name']); ?>" title="<?php echo($value[6]['name']); ?>">
                            </td>
                            <td class="stat">
                                <img src="<?php echo SERVER_URL_VISIT . $value[5]['img']?>" title="<?php echo $value[5]['name']; ?>" alt="<?php echo $value[5]['name']; ?>">
                            </td>
                            <td class="stat">
                                <?php echo $value[10]['count']; ?>
                            </td> 
                            <td class="stat">
                                <?php 
                                    if ($value[7] == "-" && $value[9] == '') {
                                        echo "-";                                           //
                                    } elseif($value[7] == "-" && $value[9] != '') {
                                        echo '<a href="' . $value[9] . '" target="_blank" title="' . $value[9] . '">' . ((isset($value[9]{81})) ? substr($value[9],0, 80) . "..." : $value[9]) . '</a>';
                                    } elseif ($value[7] != "-" && $value[8] != "") {
                                        echo '<a href="' . $value[9] . '" target="_blank" alt="' . $value[9] . '">' . $value[7] .': ' . $value[8] . '</a>';
                                    } else {
                                        echo $value[7].$value[8].$value[9];
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr id="<?php echo $hash;?>" style="display: none; "> 
                            <td colspan="6" width="100%" style="text-align: center;">
                              <div class="info_stat_all">
                                
                                     <table class="info">
                                        <tr>
                                            <td align="left">Date</td>
                                            <td align="left"><?php echo $value[4];?></td>
                                            <td align="left">Referer Searching</td> 
                                            <td align="left"><?php echo $value[7]; ?></td> 
                                        </tr>
                                        <tr>
                                            <td align="left">IP</td>
                                            <td align="left"><?php echo $value[3]['ip'];?></td>
                                            <td align="left">Referer Query</td> 
                                            <td align="left"><?php echo $value[8]; ?></td> 
                                        </tr>
                                        <tr>
                                            <td align="left">Country</td>
                                            <td align="left"><img src="<?php echo IMG . 'system/geo/' . $value[3]['country_code'] . '.gif';?>" alt="<?php echo $value[3]['country'];?>" title="<?php echo $value[3]['country'];?>"></td>
                                            <td align="left">Referer</td> 
                                            <td align="left"><?php echo $value[9]; ?></td>
                                        </tr>
                                        <tr>
                                            <td align="left">City</td>
                                            <td align="left"><?php echo $value[3]['city'];?></td>
                                            <td colspan="2"></td> 
                                        </tr>
                                        <tr>
                                            <td align="left">System</td>
                                            <td align="left"><img src="<?php echo IMG . $value[6]['img']; ?>" alt="<?php echo($value[6]['name']); ?>" title="<?php echo($value[6]['name']); ?>"></td>
                                            <td colspan="2"></td> 
                                        </tr>
                                        <tr>
                                            <td align="left">Browser</td>
                                            <td align="left"><img src="<?php echo  SERVER_URL_VISIT . $value[5]['img']?>" title="<?php echo $value[5]['name']; ?>" alt="<?php echo $value[5]['name']; ?>"></td>
                                            <td colspan="2"></td> 
                                        </tr>
                                    </table> 
                                </div>
                            </td>
                        </tr>
                        <?php }
                } ?>
            </tbody>
        </table>
    </div>
    <?php if (isset($record)) { ?> 
        <div class="block-export">
            <a href="<?php echo admin_url( 'admin-post.php?action=exportToCsv' ) ?>" target="_blank">Export to csv</a> 
        </div>
        <?php } ?>
</div>

