<?php

include "src/BankStatementParser.php";

const FILE_PATH = "data/test.txt";

$parser = new BankStatementParser(FILE_PATH);
$platezhki = $parser->parse();


print_r($platezhki);


?>