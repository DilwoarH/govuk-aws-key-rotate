<?php
$AWS_PROFILE = "govuk-{$argv[1]}";

$users = shell_exec("aws iam list-users --profile {$AWS_PROFILE}");
$users = json_decode($users, true);
$users = $users['Users'];

//Give our CSV file a name.
$csvFileName = "aws-users-{$argv[1]}.csv";
 
//Open file pointer.
$fp = fopen($csvFileName, 'w');
fputcsv($fp, [
    "UserName", "UserID", "Arn", 
    "Key_1", "Status_1", "Created_1", "LastUsed_1",
    "Key_2", "Status_2", "Created_2", "LastUsed_2",
    "Key_3", "Status_3", "Created_3", "LastUsed_3",
]);

//Loop through the associative array.
foreach($users as $user){
    //Write the row to the CSV file.
    $access_keys = shell_exec("aws iam list-access-keys --profile {$AWS_PROFILE} --user-name {$user['UserName']}");
    $access_keys = json_decode($access_keys, true);
    $access_keys = $access_keys['AccessKeyMetadata'];

    $row = [
        "UserName"  => $user['UserName'],
        "UserId"    => $user['UserId'],
        "Arn"       => $user["Arn"],
    ];

    $count = 1;

    foreach( $access_keys as $key )
    {
        $lastUsed = shell_exec("aws iam get-access-key-last-used --access-key-id {$key["AccessKeyId"]} --profile {$AWS_PROFILE}");
        $lastUsed = json_decode($lastUsed, true);

        $row["Key_{$count}"] = $key["AccessKeyId"];
        $row["Status_{$count}"] = $key["Status"];
        $row["Created_{$count}"] = $key["CreateDate"];
        $row["LastUsed_{$count}"] = $lastUsed["AccessKeyLastUsed"]["LastUsedDate"];
        $count++;
    }

    fputcsv($fp, $row);
}
 
//Finally, close the file pointer.
fclose($fp);
