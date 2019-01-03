<?php

$users = shell_exec("aws iam list-users --profile govuk-{$argv[1]}");
$users = json_decode($users, true);
$users = $users['Users'];

//Give our CSV file a name.
$csvFileName = "aws-users-{$argv[1]}.csv";
 
//Open file pointer.
$fp = fopen($csvFileName, 'w');
fputcsv($fp, ["UserName", "UserID", "Arn", "CreatedDate"]);

//Loop through the associative array.
foreach($users as $row){
    //Write the row to the CSV file.
    unset($row['Path']);
    fputcsv($fp, $row);
}
 
//Finally, close the file pointer.
fclose($fp);
