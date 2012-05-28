<?php

require 'config.php';
require 'localize.php';
    
header('Content-Type: text/plain; charset=utf-8');

$monitor = new LiveMonitor($iface_list[0], $liveRunningTemplate, $liveStoppedTemplate);
$result  = $monitor->Run();


if(isset($result))
    echo $result;


/**
 * Should work with a basic Linux installation.
 * Live Monitoring for interfaces using space character will not work correctly.
 * Programs used are : ps, whoami, grep, awk, timeout and vnstat of course
 */
class LiveMonitor
{
    /* @var string */
    private $_pidVnstatCmd = "ps -fu $(whoami) | grep vnstat | grep {0} | grep -v timeout | awk '{print $2}'";
    /* @var string */
    private $_ifListVnstatCmd = "vnstat --iflist | awk '{print $3,$4,$5}'";
    /* @var string */
    private $_interface;
    /* @var string */
    private $_tempDir;
    /* @var string */
    private $_pidFile;
    /* @var string */
    private $_liveFile;
    /* @var string */
    private $_runningTemplate;
    /* @var string */    
    private $_stoppedTemplate;

    /**
     * @param string $defaultInterface
     * @param string $runningTemplate
     * @param string $stoppedTemplate
     */
    public function LiveMonitor($defaultInterface, $runningTemplate, $stoppedTemplate)
    {
        $this->_runningTemplate = $runningTemplate;
        $this->_stoppedTemplate = $stoppedTemplate;

        $this->_tempDir = dirname(__FILE__) . '/tmp';

        if(!file_exists($this->_tempDir))
            mkdir($this->_tempDir);
            
        $this->_pidFile  = $this->_tempDir . '/vnstat.pid';
        $this->_liveFile = $this->_tempDir . '/vnstat.live';

        if(isset($_POST['if']))
            $this->_interface = $_POST['if'];
        else
            $this->_interface = $defaultInterface;
    }
    
    /**
     * @return string
     */
    public function Run()
    {
        $vnstatIfList = exec($this->_ifListVnstatCmd);
        $vnstatIfListArray = explode(' ', $vnstatIfList);

        if(!empty($this->_interface) && in_array($this->_interface, $vnstatIfListArray))
        {
            if(isset($_POST['action']))
            {
                switch($_POST['action'])
                {
                    case 'start':
                        $output = $this->StartVnstat();
                        break;

                    case 'stop':
                        $this->StopVnstat();
                        break;
                }
            }

            $this->CheckVnstatProcess();

            if(!isset($output))
            {
                if(file_exists($this->_liveFile.$this->_interface) &&
                   file_exists($this->_pidFile.$this->_interface))
                {
                    $output = $this->ViewLive();
                }
                else
                {
                    $link = "<a href=\"javascript:{$this->_interface}.start()\">" . T('Start Live') . '</a>';
                    $text = T('Live monitoring not running on interface ') . $this->_interface;
                    
                    $output = $this->_stoppedTemplate;
                    $output = str_replace('{link}', $link, $output);
                    $output = str_replace('{text}', $text, $output);
                }
            }

            return isset($output) ? $output : null;
        }
    }

    /**
     *
     */
    private function CheckVnstatProcess()
    {
        $realPid = trim(exec(str_replace('{0}', $this->_interface, $this->_pidVnstatCmd)));

        if(empty($realPid))
        {
            $this->ClearTmpFiles();
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

            exec(str_replace('{0}', $this->_interface, $this->_pidVnstatCmd) . ' > ' . $this->_pidFile.$this->_interface);

            return T('Live monitoring started on interface ') . $this->_interface;
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

            $this->ClearTmpFiles();
        }
    }

    /**
     *
     */
    private function ClearTmpFiles()
    {
        if(file_exists($this->_pidFile.$this->_interface))
            unlink($this->_pidFile.$this->_interface);

        if(file_exists($this->_liveFile.$this->_interface))
            unlink($this->_liveFile.$this->_interface);
    }

    /**
     * @return string
     */
    private function ViewLive()
    {
        $liveFileContent = file_get_contents($this->_liveFile.$this->_interface);

        $matched = preg_match('/getting.*(rx:.*)$/', $liveFileContent, $lastRecord);

        if($matched)
        {
            $trimmedLastRecord  = trim(str_replace(' ', '', $lastRecord[1]));
            $genericPatternPart = '(\d+\.?\d*)(.*\/s)(\d+\.?\d*)(.*)';

            preg_match("/rx:{$genericPatternPart}tx:/", $trimmedLastRecord, $matches);

            $link = "<a href='javascript:{$this->_interface}.stop();'>" . T('Stop Live') . '</a>';
            
            $output = $this->_runningTemplate;
            $output = str_replace('{link}', $link, $output);
            $output = str_replace('{rxText}', T('Reception'), $output);
            $output = str_replace('{rxRate}', $matches[1], $output);
            $output = str_replace('{rxRateUnit}', $matches[2], $output);
            $output = str_replace('{rxSum}', $matches[3], $output);
            $output = str_replace('{rxSumUnit}', $matches[4], $output);

            preg_match("/tx:{$genericPatternPart}$/", $trimmedLastRecord, $matches);

            $output = str_replace('{txText}', T('Transmission'), $output);
            $output = str_replace('{txRate}', $matches[1], $output);
            $output = str_replace('{txRateUnit}', $matches[2], $output);
            $output = str_replace('{txSum}', $matches[3], $output);
            $output = str_replace('{txSumUnit}', $matches[4], $output);

            return $output;
        }
        return null;
    }
}
?>