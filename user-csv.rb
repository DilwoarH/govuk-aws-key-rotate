# Example: ruby user-csv.rb govuk-integration

require 'json'
require 'csv'

if !ARGV[0]
  abort('No profile passed in, please pass an aws profile in.')
end

def aws_iam(command, additional_params = "")
  response = `aws iam #{command} --profile #{AWS_PROFILE} #{additional_params}`
  JSON.parse(response)
end

AWS_PROFILE = ARGV[0]
users = aws_iam("list-users")
users = users["Users"]

print "#{users.size} users found.\n"

file_name = "aws-users-#{AWS_PROFILE}.csv"

print "Generating CSV with filename: #{file_name}\n"

CSV.open(file_name, "w") do |csv|
  csv << [
    "UserName", "UserID", "Arn",
    "Key_1", "Status_1", "Created_1", "LastUsed_1",
    "Key_2", "Status_2", "Created_2", "LastUsed_2",
    "Key_3", "Status_3", "Created_3", "LastUsed_3",
  ]

  users.each do |user|
    print '.'

    access_keys = aws_iam("list-access-keys", "--user-name #{user['UserName']}")
    access_keys = access_keys["AccessKeyMetadata"]

    row = [user["UserName"], user["UserId"], user["Arn"]]
    access_keys.each do |key|
      lastUsed = aws_iam("get-access-key-last-used", "--access-key-id #{key["AccessKeyId"]}")
      row.push(
        key["AccessKeyId"],
        key["Status"],
        key["CreateDate"],
        lastUsed["AccessKeyLastUsed"]["LastUsedDate"]
      )
    end
    csv << row
  end
end
print "\nCSV generated: #{file_name}\n"
