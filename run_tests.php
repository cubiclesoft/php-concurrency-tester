<?php
	// PHP concurrency tester primarily for custom TCP/IP services.
	// (C) 2019 CubicleSoft.  All Rights Reserved.

	if (!isset($_SERVER["argc"]) || !$_SERVER["argc"])
	{
		echo "This file is intended to be run from the command-line.";

		exit();
	}

	// Temporary root.
	$rootpath = str_replace("\\", "/", dirname(__FILE__));

	require_once $rootpath . "/support/cli.php";
	require_once $rootpath . "/support/process_helper.php";

	// Process the command-line options.
	$options = array(
		"shortmap" => array(
			"h" => "header",
			"?" => "help"
		),
		"rules" => array(
			"header" => array("arg" => true),
			"help" => array("arg" => false)
		)
	);
	$args = CLI::ParseCommandLine($options);

	if (isset($args["opts"]["help"]) || count($args["params"]) < 2)
	{
		echo "The custom TCP/IP service concurrency testing command-line tool\n";
		echo "Purpose:  Run a PHP script that communicates with a TCP/IP server and collect output in CSV format for analysis.\n";
		echo "\n";
		echo "Syntax:  " . $args["file"] . " [options] PHPFile ConcurrencyLevel\n";
		echo "Options:\n";
		echo "\t-h   A header in CSV format.  Will be output as-is.\n";
		echo "\n";
		echo "Example:\n";
		echo "\tphp " . $args["file"] . " -h info_per_sec,num_info,num_errors test.php 20\n";

		exit();
	}

	$phpfile = realpath($args["params"][0]);
	if ($phpfile === false)  CLI::DisplayError("The file '" . $args["params"][0] . "' does not exist.");

	// Queue up the specified number of concurrent processes.
	$starttime = time() + 5;
	$procs = array();
	for ($x = 0; $x < (int)$args["params"][1]; $x++)
	{
		$cmd = escapeshellarg(PHP_BINARY) . " " . escapeshellarg($phpfile) . " " . $starttime;
		$result = ProcessHelper::StartProcess($cmd, array("stdin" => false, "stderr" => false));
		if (!$result["success"])
		{
			var_dump($result);
			exit();
		}

		unset($result["info"]);
		$result["read"] = "";

		$procs[] = $result;
	}

	// Output the CSV header (if specified).
	if (isset($args["opts"]["header"]))  echo $args["opts"]["header"] . "\n";

	// Wait for the queued processes to complete and collect the results.
	while (count($procs))
	{
		sleep(1);

		// Read all of the process stdout handles.
		foreach ($procs as $num => $info)
		{
			$data = @fread($info["pipes"][1], 4096);
			if ($data != "")
			{
				$info["read"] .= $data;

				$procs[$num] = $info;
			}
			else if (feof($info["pipes"][1]))
			{
				$pinfo = @proc_get_status($info["proc"]);
				if (!$pinfo["running"])
				{
					echo $info["read"];

					fclose($info["pipes"][1]);
					proc_close($info["proc"]);

					unset($procs[$num]);
				}
			}
		}
	}
?>