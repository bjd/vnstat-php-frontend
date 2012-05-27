<?php

require 'config.php';
require 'localize.php';
    
header('Content-Type: text/html; charset=utf-8');

$monitor = new LiveMonitor($iface_list[0]);
$result  = $monitor->Run();


if(isset($result))
    echo $result;


/**
 * Should work with a basic Linux installation.
 * Programs used are : ps, whoami, grep, awk, timeout and vnstat of course
 */
class LiveMonitor
{
    /* @var string */
    private $_pidVnstatCmd = "ps -u $(whoami) | grep 'vnstat' | awk '{print $1}'";
    /* @var string */
    private $_interface;
    /* @var string */
    private $_tempDir;
    /* @var string */
    private $_pidFile;
    /* @var string */
    private $_liveFile;
    /* @var string */    
    private $_tpl = '<b>{rxText}</b>
                     <br/>{rxRate} {rxRateUnit}
                     <br/>{rxSum} {rxSumUnit}<br/>
                     <br/>
                     <b>{txText}</b>
                     <br/>{txRate} {txRateUnit}
                     <br/>{txSum} {txSumUnit}';

    /**
     * @param string $defaultInterface
     */
    public function LiveMonitor($defaultInterface)
    {
        $this->_tempDir = dirname(__FILE__) . '/tmp';

        if(!file_exists($this->_tempDir))
            mkdir($this->_tempDir);
            
        $this->_pidFile  = $this->_tempDir . '/vnstat.pid';
        $this->_liveFile = $this->_tempDir . '/vnstat.live';

        if(isset($_GET['if']))
            $this->_interface = $_GET['if'];
        else
            $this->_interface = $defaultInterface;
    }
    
    /**
     * @return string
     */
    public function Run()
    {
        if(!empty($this->_interface))
        {
            if(isset($_GET['action']))
            {
                switch($_GET['action'])
                {
                    case 'start':
                        $result = $this->StartVnstat();
                        break;

                    case 'stop':
                        $this->StopVnstat();
                        break;
                }
            }

            $this->CheckVnstatProcess();

            if(!isset($result))
            {
                if(file_exists($this->_liveFile.$this->_interface) &&
                   file_exists($this->_pidFile.$this->_interface))
                {
                    $result = $this->ViewLive();
                }
                else
                    $result = 'vnstat live monitoring not running on interface ' . $this->_interface;
            }

            return isset($result) ? $result : null;
        }
    }

    /**
     *
     */
    private function CheckVnstatProcess()
    {
        $realPid = trim(exec($this->_pidVnstatCmd));

        if(empty($realPid))
        {
            if(file_exists($this->_pidFile.$this->_interface))
                unlink($this->_pidFile.$this->_interface);

            if(file_exists($this->_liveFile.$this->_interface))
                unlink($this->_liveFile.$this->_interface);
        }
        else
        {
            if(file_exists($this->_pidFile.$this->_interface))
                $pid = trim(file_get_contents($this->_pidFile.$this->_interface));

            if(!isset($pid) || $realPid != $pid)
                file_put_contents($this->_pidFile.$this->_interface, $realPid);
        }
    }

    /**
     * @return string
     */
    private function StartVnstat()
    {
        if(!file_exists($this->_pidFile.$this->_interface))
        {
            exec("timeout --signal=KILL 1h vnstat -l 1 -i {$this->_interface} > {$this->_liveFile}{$this->_interface} 2> /dev/null &");

            exec($this->_pidVnstatCmd . ' > ' . $this->_pidFile.$this->_interface);

            return 'vnstat live monitoring started on interface ' . $this->_interface;
        }
        return null;
    }

    /**
     *
     */
    private function StopVnstat()
    {
        if(file_exists($this->_pidFile.$this->_interface))
        {
            $pid = trim(file_get_contents($this->_pidFile.$this->_interface));

            posix_kill($pid, 9);
        }
    }

    /**
     * @return string
     */
    private function ViewLive()
    {
        $out = file_get_contents($this->_liveFile.$this->_interface);

        $matched = preg_match('/getting.*(rx:.*)$/', $out, $lastRecord);

        if($matched)
        {
            $trimmedLastRecord  = trim(str_replace(' ', '', $lastRecord[1]));
            $genericPatternPart = '(\d+\.?\d*)(.*\/s)(\d+\.?\d*)(.*)';

            preg_match("/rx:{$genericPatternPart}tx:/", $trimmedLastRecord, $matches);

            $htmlOut = $this->_tpl;
            $htmlOut = str_replace('{rxText}', T('Reception'), $htmlOut);
            $htmlOut = str_replace('{rxRate}', $matches[1], $htmlOut);
            $htmlOut = str_replace('{rxRateUnit}', $matches[2], $htmlOut);
            $htmlOut = str_replace('{rxSum}', $matches[3], $htmlOut);
            $htmlOut = str_replace('{rxSumUnit}', $matches[4], $htmlOut);

            preg_match("/tx:{$genericPatternPart}$/", $trimmedLastRecord, $matches);

            $htmlOut = str_replace('{txText}', T('Transmission'), $htmlOut);
            $htmlOut = str_replace('{txRate}', $matches[1], $htmlOut);
            $htmlOut = str_replace('{txRateUnit}', $matches[2], $htmlOut);
            $htmlOut = str_replace('{txSum}', $matches[3], $htmlOut);
            $htmlOut = str_replace('{txSumUnit}', $matches[4], $htmlOut);

            return $htmlOut;
        }
        return null;
    }
}
?>