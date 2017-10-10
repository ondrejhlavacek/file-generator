<?php

ini_set('display_errors', true);

require_once __DIR__ . '/vendor/autoload.php';

$arguments = getopt("d::", array("data::"));
$dataFolder = "/data";
if (isset($arguments["data"])) {
    $dataFolder = $arguments["data"];
}
$config = json_decode(file_get_contents($dataFolder . "/config.json"), true)["parameters"];

$chars = [
    "\t", "\n", "a", "b", "c", "d", "e", "f"
];

function generateCell($bytes, $chars) {
    $cell = "";
    for ($j = 0; $j < $bytes; $j++) {
        $cell .= $chars[mt_rand(0, count($chars) - 1)];
    }
    return $cell;
}

function generateFile($file, $row, $rowCount) {
    if (!file_exists(dirname($file))) {
        mkdir(dirname($file), 0777, true);
    }
    touch($file);
    $fh = fopen($file, "w+");
    for ($i = 0; $i < $rowCount; $i++) {
        fputs($fh, $row);
    }
    fclose($fh);
}

$k1row = generateCell(1000, $chars);
$k10row = generateCell(10000, $chars);
$k100row = generateCell(100000, $chars);

$matrix = $config["matrix"];
foreach ($matrix as $key => $matrixItem) {
    $newRow = "";
    switch($matrixItem["row"]) {
        case "k1row":
            $newRow = $k1row;
            break;
        case "k10row":
            $newRow = $k10row;
            break;
        case "k100row":
            $newRow = $k100row;
            break;
        default:
            throw new \Exception("invalid row identifier");
            break;
    }
    $matrix[$key]["row"] = $newRow;
}


foreach($matrix as $parameters) {
    $temp = new Keboola\Temp\Temp();

    if ($parameters["files"] == 1) {
        generateFile($dataFolder . "/" . $parameters["destination"], $parameters["row"], $parameters["rows"]);
    } else {
        for ($i = 0; $i < $parameters["files"]; $i++) {
            generateFile($dataFolder . "/" . $parameters["destination"] . "-" . $i, $parameters["row"], $parameters["rows"]);
        }
    }
}

