<?php
    //
    // vnStat PHP frontend (c)2006-2010 Bjorge Dijkstra (bjd@jooz.net)
    //
    // This program is free software; you can redistribute it and/or modify
    // it under the terms of the GNU General Public License as published by
    // the Free Software Foundation; either version 2 of the License, or
    // (at your option) any later version.
    //
    // This program is distributed in the hope that it will be useful,
    // but WITHOUT ANY WARRANTY; without even the implied warranty of
    // MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    // GNU General Public License for more details.
    //
    // You should have received a copy of the GNU General Public License
    // along with this program; if not, write to the Free Software
    // Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
    //
    //
    // see file COPYING or at http://www.gnu.org/licenses/gpl.html
    // for more information.
    //
    require 'config.php';
    require 'localize.php';
    require 'vnstat.php';

    validate_input();

    require "./themes/$style/theme.php";

    function write_side_bar()
    {
        global $iface, $page, $graph, $script, $style;
        global $iface_list, $iface_title;
        global $page_list, $page_title;

        $p = "&amp;graph=$graph&amp;style=$style";

        print "<ul class=\"iface\">\n";
        foreach ($iface_list as $if)
        {
            if ($iface == $if) {
                print "<li class=\"iface active\">";
            } else {
                print "<li class=\"iface\">";
            }
            print "<a href=\"$script?if=$if$p\">";
            if (isset($iface_title[$if]))
            {
                print $iface_title[$if];
            }
            else
            {
                print $if;
            }
            print "</a>";
            print "<ul class=\"page\">\n";
            foreach ($page_list as $pg)
            {
                print "<li class=\"page\"><a href=\"$script?if=$if$p&amp;page=$pg\">".$page_title[$pg]."</a></li>\n";
            }
            print "</ul></li>\n";
        }
        print "</ul>\n";
    }


    function kbytes_to_string($kb)
    {
        $units = array('TB','GB','MB','KB');
        $scale = 1024*1024*1024;
        $ui = 0;

        while (($kb < $scale) && ($scale > 1))
        {
            $ui++;
            $scale = $scale / 1024;
        }
        return sprintf("%0.2f %s", ($kb/$scale),$units[$ui]);
    }

    function write_summary()
    {
        global $summary,$top,$day,$hour,$month;

        $trx = $summary['totalrx']*1024+$summary['totalrxk'];
        $ttx = $summary['totaltx']*1024+$summary['totaltxk'];

        //
        // build array for write_data_table
        //
        $sum['hour']['act'] = 1;
        $sum['hour']['rx'] = $hour[0]['rx'];
        $sum['hour']['tx'] = $hour[0]['tx'];

        $sum['day']['act'] = 1;
        $sum['day']['rx'] = $day[0]['rx'];
        $sum['day']['tx'] = $day[0]['tx'];

        $sum['month']['act'] = 1;
        $sum['month']['rx'] = $month[0]['rx'];
        $sum['month']['tx'] = $month[0]['tx'];

        $sum['total']['act'] = 1;
        $sum['total']['rx'] = $trx;
        $sum['total']['tx'] = $ttx;

        print json_encode($sum);
    }


    get_vnstat_data(false);

    header('Content-type: text/json; charset=utf-8');
    $graph_params = "if=$iface&amp;page=$page&amp;style=$style";
    if ($page == 's')
    {
        write_summary();
    }
    else if ($page == 'h')
    {
      print json_encode(array('hours' => $hour));
    }
    else if ($page == 'd')
    {
      print json_encode(array('days' => $day));
    }
    else if ($page == 'm')
    {
      print json_encode(array('months' => $month));
    }
    ?>