<?
$wmi = new COM("Winmgmts://");
$cpus = $wmi->InstancesOf("Win32_Processor");
$os = $wmi->InstancesOf("Win32_OperatingSystem");
$os = $os->Next();
$os = $os->Caption." - ".$os->CSDVersion." ".$os->Version;
$system = $wmi->InstancesOf("Win32_ComputerSystem");
$system = $system->Next();
$cpucount = $system->NumberOfProcessors;

$ram = $wmi->InstancesOf("Win32_LogicalMemoryConfiguration");
$ram = $ram->Next();
$ramtotal = $ram->TotalPhysicalMemory*1024;

$ram = $wmi->InstancesOf("Win32_PerfRawData_PerfOS_Memory");
$ram = $ram->Next();
$ramused = $ramtotal-$ram->AvailableBytes;
$ramused = mksize($ramused);
$ramtotal = mksize($ramtotal);

$uptime = $wmi->InstancesOf("Win32_PerfFormattedData_PerfOS_System");
$uptime = $uptime->Next();
$uptime = mkprettytime2($uptime->SystemUpTime);

while ($cpu = $cpus->Next()) {
	$cpus1[] = $cpu->LoadPercentage;
	$totalusage += $cpu->LoadPercentage;
}
	
$totalusage = round($totalusage/$cpucount, 2);	

echo "<b>OS:</b> $os<BR>";
echo "<b>Number of CPUs:</b> $cpucount<BR>";
for ($i=0;$i<count($cpus1);$i++)
	echo "<b>CPU$i Usage:</b> $cpus1[$i]%<BR>";
echo "<b>Total CPU Usage:</b> $totalusage%<BR>";
echo "<B>RAM Usage:</b> $ramused/$ramtotal<BR>";
echo "<b>Uptime:</b> $uptime";
?>