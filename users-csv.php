<?php
$AWS_PROFILE = "govuk-{$argv[1]}";

$users = shell_exec("aws iam list-users --profile {$AWS_PROFILE}");
$users = json_decode($users, true);
$users = $users['Users'];

//Give our CSV file a name.
$csvFileName = "aws-users-{$argv[1]}.csv";
 
//Open file pointer.
$fp = fopen($csvFileName, 'w');
fputcsv($fp, ["UserName", "UserID", "Arn", "Key", "Status", "Created"]);

//Loop through the associative array.
foreach($users as $user){
    //Write the row to the CSV file.
    $access_keys = shell_exec("aws iam list-access-keys --profile {$AWS_PROFILE} --user-name {$user['UserName']}");
    $access_keys = json_decode($access_keys, true);
    $access_keys = $access_keys['AccessKeyMetadata'];

    foreach( $access_keys as $key )
    {
        $row = [
            "UserName"  => $user['UserName'],
            "UserId"    => $user['UserId'],
            "Arn"       => $user["Arn"],
            "Key"       => $key["AccessKeyId"],
            "Status"    => $key["Status"],
            "Created"   => $key["CreateDate"],
        ];
        fputcsv($fp, $row);
    }

}
 
//Finally, close the file pointer.
fclose($fp);
