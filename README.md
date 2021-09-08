# CLI User Importer
## Description
A small CLI App for inserting data from a .csv file. The CSV file must having the following.

| name | surname | email |
|------|---------|-------|
| John | Doe      | jd@email.com |

## Installation Instructions
This script was tested using PHP7.2 and Mariadb 10.6.4. If you want to use the exact environment I used, I use a docker setup found [here](https://github.com/lifeofdan/docker_env). Setup instructions for that are found at that repo and are outside the scope of this application.

Once you have your environment running and a database set up and ready to import to you are ready.

1. Clone the php_code_challenge repo. ` git clone https://github.com/lifeofdan/php_code_challenge.git`
2. Put your .csv file into the repo.
3. On first run, you will want to run the file with --create_table in order to create the database table in preparation for importing the .csv file contents.

For example:
`php user_upload.php -h {dbhostname} -u {dbusername} -p {dbpassword} -n {dbname} --create_table`

You should get some feedback saying the the table was created or an error that the table already exists if you ran the command twice.

4. Use the --file command to specify the file to import from.

For example:
`php user_upload.php -h {dbhostname} -u {dbusername} -p {dbpassword} -n {dbname} --file {filename}`

*(If you want to do a "dry run" just add --dry_run to the previous command. This will run the import without adding these items to the database)*

## Full List of Parameters
To get a full list of the avaialable options, just type --help.

For example:
`php user_upload.php --help`

```
	-h [hostname]		Specify database host name.
	-u [username]		Specify database user name.
	-p [password]		Specify database password.
	-n [databasename]	Specify database name.
	--create_table 		Create the Users table. This only needs to be run this once.
	--dry_run 		Used with the --file option to run the command without a database insert.
	--file [filename]	Specify the file you want to import.
	--help 			Displays help instructions.
```