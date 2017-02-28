<?php

snmp_set_quick_print(1);

$sys_name = snmp2_get ("127.0.0.1", "public", "SNMPv2-MIB::sysName.0");
$cpu_idle = snmp2_get ("127.0.0.1", "public", "UCD-SNMP-MIB::ssCpuIdle.0");
$mem_free = snmp2_get ("127.0.0.1", "public", "UCD-SNMP-MIB::memAvailReal.0");

$tcp_v6_ar = snmp2_real_walk("127.0.0.1", "public", "TCP-MIB::tcpListenerTable");

$run_ar = snmp2_real_walk("127.0.0.1", "public", "HOST-RESOURCES-MIB::hrSWRunName");
$xinetd = "\"xinetd\"";
$xinetd_key = array_search($xinetd, $run_ar);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<style type="text/css">
table.vnc {
        border-width: medium;
        border-spacing: 0px;
        border-style: none;
        border-color: black;
        border-collapse: collapse;
        background-color: white;
        margin:1em auto;
}
table.vnc th {
        border-width: 1px;
        padding: 3px;
        border-style: solid;
        border-color: black;
        background-color: white;
        -moz-border-radius: ;
}
table.vnc td {
        border-width: 1px;
        padding: 3px;
        border-style: solid;
        border-color: black;
        background-color: white;
        -moz-border-radius: ;
}
table.sys {
        border-width: medium;
        border-spacing: 5px;
        border-style: hidden;
        border-color: black;
        border-collapse: collapse;
        background-color: white;
        margin:1em auto;
}
table.sys th {
        border-width: thin;
        padding: 5px;
        border-style: solid;
        border-color: black;
        background-color: white;
        -moz-border-radius: ;
}
table.sys td {
        border-width: thin;
        padding: 5px;
        border-style: solid;
        border-color: black;
        background-color: white;
        -moz-border-radius: ;
}
footer {
    display: block;
}
</style>
<head>
  <title></title>
  <meta name="GENERATOR" content="Genrator">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<h3 style="text-align:center;">
<?php
echo strtoupper($sys_name);
?>
</h3>
<hr>
 <table class="sys">
  <thead>
    <tr>
      <th>&nbsp;Hostname&nbsp;&nbsp;&nbsp;</th>
      <th>&nbsp;CPU Idle&nbsp;&nbsp;&nbsp;</th>
      <th>&nbsp;Available Memory&nbsp;&nbsp;&nbsp;&nbsp;</th>
      <th>&nbsp;Xinetd status (pid)&nbsp;</th>
    </tr>
  </thead>
  <tbody>
   <tr>
<?php
echo "<td>&nbsp;";
echo $sys_name;
echo "&nbsp;</td>";
echo "<td>&nbsp;";
echo $cpu_idle."%";
echo "&nbsp;</td>";
echo "<td>&nbsp;";
echo $mem_free;
echo "&nbsp;</td>";
echo "<td>&nbsp;";
if($xinetd_key){
    $pid_ar = explode(".", $xinetd_key);
    $xinetd_pid = $pid_ar[1];
    echo "<b><font color=\"green\">Running</font></b>&nbsp;($xinetd_pid)";
}
else{
echo "</b><font color=\"red\">Stopped</font></b>";
}

echo "&nbsp;</td>";
?>
   </tr>
  </tbody>
 </table>
<table class="vnc">
  <thead>
    <tr>
      <th>&nbsp;VNC #&nbsp;</th>
      <th>&nbsp;username&nbsp;</th>
      <th>&nbsp;Connection&nbsp;</th>
      <th>&nbsp;xinetd&nbsp;<br>&nbsp;config&nbsp;</th>
      <th>&nbsp;Service Port&nbsp;</th>
      <th>&nbsp;Desktop&nbsp;<br>&nbsp;Session&nbsp;</th>
      <th>&nbsp;VNC Status (pid)&nbsp;</th>
    </tr>
  </thead>

  <tbody>
<?php
for ($i = 1; $i <= 99; $i++) {
    $vncfile = '/etc/xinetd.d/vnc'.$i;
    if (file_exists($vncfile)) {
        $pattern = "/user = * /";
        $usr_cfg_line = preg_grep($pattern, file($vncfile));
        foreach ($usr_cfg_line as $key =>  $val) {
            $aa = $val;
        }
        $usr_str = preg_split("/[\s,]+/", $aa);
        //$usr_str = explode( "=", $aa);
        echo "<tr>";
        echo "<td> &nbsp;".$i."&nbsp; </td>";
        echo "<td> &nbsp;".$usr_str[3]."&nbsp; </td>";
        echo "<td> &nbsp;".gethostname().":".$i."&nbsp; </td>";
        echo "<td> &nbsp;";
        $cfg_pattern1 = "/^service vnc".$i."/";
        $cfg_pattern2 = "#/home/".$usr_str[3]."/.vnc/passwd#";
        $cfg_pattern3 = "#/home/".$usr_str[3]."/.Xauthority#";
        $cfg_ln1 = preg_grep($cfg_pattern1, file($vncfile));
        $cfg_ln2 = preg_grep($cfg_pattern2, file($vncfile));
        $cfg_ln3 = preg_grep($cfg_pattern3, file($vncfile));
        if($cfg_ln1 && $cfg_ln2){
            echo "<b><font color=\"green\">OK</font></b>&nbsp;";
        }
        else{
            echo "<b><font color=\"red\">Error:</font></b>&nbsp;";
        }
        echo "</td>";
        $srv_pattern = "/^vnc".$i." /";
        $srv_ln = preg_grep($srv_pattern, file("/etc/services"));
        echo "<td> &nbsp;";
        if($srv_ln){
            foreach ($srv_ln as $key =>  $val) {
                $ln1 = $val;
                $srv_port_ar = preg_split("/[\s,]+/", $ln1);
                echo "<b><font color=\"green\">OK</font></b>&nbsp;".$srv_port_ar[1]."&nbsp;";
            }
        }
        else {
            echo "<b><font color=\"red\">Error:</font></b> no port defined&nbsp;";
        }
        echo "</td>";
        $dmrc_pattern = '/\b(Session=LXDE|Session=jwm)\b/';
        $dmrc_ln = preg_grep($dmrc_pattern, file("/var/cache/lightdm/dmrc/".$usr_str[3].".dmrc"));
        echo "<td> &nbsp;";
        if($dmrc_ln){
            echo "<b><font color=\"green\">OK</font></b>&nbsp; ";
            foreach ($dmrc_ln as $key =>  $val) {
                $xses = $val;
                echo $xses."&nbsp;";
            }
        }
        else {
            echo "<b><font color=\"red\">Error:</font></b>&nbsp;";
        }
        echo "</td>";
        echo "<td>&nbsp;";
        if ($tcp_v6_ar){
            $tcp_port = 5900 + $i;
            $pid = $tcp_v6_ar["TCP-MIB::tcpListenerProcess.ipv6.\"00:00:00:00:00:00:00:00:00:00:00:00:00:00:00:00\".".$tcp_port];
            if ($pid){
                if ($pid == $xinetd_pid){
                    echo "<b><font color=\"orange\">Waiting</font></b> (xinetd)&nbsp;";
                }
                else{
                    echo "<b><font color=\"green\">Running</font></b> ($pid)&nbsp;";
                }
            }
            else{
                echo "<b><font color=\"red\">Error:</font></b> null pid&nbsp;";
            }
        }
        else{
            echo "<b><font color=\"gray\">Unknown:</font></b> no data&nbsp;";
        }
        echo "</td>";
        echo "</tr>";
    }
}

?>
   <tr>
   </tr>
   <tr style="border-style: hidden;">
    <td colspan="7" style="border-style: hidden;">
      If you are having trouble with VNC contact IT support
      <br>
<?php
echo "Page gentreated on ".date(DATE_RSS);
?>
    </td>
   </tr>
  </tbody>
</table>
</body>
<footer>
</footer>
</html>
