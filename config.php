<?php
    //
    // vnStat PHP frontend 1.4.1 (c)2006-2008 Bjorge Dijkstra (bjd@jooz.net)
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
    error_reporting(E_ALL | E_NOTICE);

    //
    // configuration parameters
    //
    // edit these to reflect your particular situation
    //
    setlocale(LC_ALL, 'en_US.UTF-8');

    // list of network interfaces monitored by vnStat
    $iface_list = array('eth0', 'eth1', 'sixxs');

    //
    // optional names for interfaces
    // if there's no name set for an interface then the interface identifier
    // will be displayed instead
    //    
    $iface_title['eth0'] = 'Internal';
    $iface_title['eth1'] = 'Internet';
    $iface_title['sixxs'] = 'SixXS IPv6';

    //
    // There are two possible sources for vnstat data. If the $vnstat_bin
    // variable is set then vnstat is called directly from the PHP script
    // to get the interface data. 
    //
    // The other option is to periodically dump the vnstat interface data to
    // a file (e.g. by a cronjob). In that case the $vnstat_bin variable
    // must be cleared and set $data_dir to the location where the dumps
    // are stored. Dumps must be named 'vnstat_dump_$iface'.
    //
    // You can generate vnstat dumps with the command:
    //   vnstat --dumpdb -i $iface > /path/to/data_dir/vnstat_dump_$iface
    // 
    $vnstat_bin = '';
    $data_dir = './dumps';

    // graphics format to use: svg or png
    $graph_format='svg';
    
    // Font to use for PNG graphs
    define('GRAPH_FONT',dirname(__FILE__).'/VeraBd.ttf');

    // Font to use for SVG graphs
    define('SVG_FONT', 'Verdana');

    // color schemes
    // colors are defined as R,G,B,ALPHA quads where R, G and B range from 0-255
    // and ALPHA from 0-127 where 0 is opaque and 127 completely transparent.
    //
    define('DEFAULT_COLORSCHEME', 'light');

    $colorscheme['light'] = array(
         'stylesheet'         => 'vnstat.css',
         'image_background'   => array( 255, 255, 255,   0 ),
	 'graph_background'   => array( 220, 220, 230,   0 ),
	 'graph_background_2' => array( 205, 205, 220,   0 ),
	 'grid_stipple_1'     => array( 140, 140, 140,   0 ),
         'grid_stipple_2'     => array( 200, 200, 200,   0 ),
	 'border'             => array(   0,   0,   0,   0 ),
	 'text'               => array(   0,   0,   0,   0 ),
	 'rx'                 => array( 190, 190,  20,  50 ),
	 'rx_border'	      => array(  40,  80,  40,  90 ),
	 'tx'	              => array( 130, 160, 100,  50 ),
	 'tx_border'          => array(  80,  40,  40,  90 )
     );

    // A red colorscheme based on a contribution by Enrico Tröger
    $colorscheme['red'] = array(
         'stylesheet'         => 'vnstat_red.css',
         'image_background'   => array( 225, 225, 225,   0 ),
	 'graph_background'   => array( 220, 220, 230,   0 ),
	 'graph_background_2' => array( 205, 205, 220,   0 ),
	 'grid_stipple_1'     => array( 140, 140, 140,   0 ),
         'grid_stipple_2'     => array( 200, 200, 200,   0 ),
	 'border'             => array(   0,   0,   0,   0 ),
	 'text'               => array(   0,   0,   0,   0 ),
	 'rx'                 => array( 190,  20,  20,  50 ),
	 'rx_border'	      => array(  80,  40,  40,  90 ),
	 'tx'	              => array( 130, 130, 130,  50 ),
	 'tx_border'          => array(  60,  60,  60,  90 )
     );
?>
