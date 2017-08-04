import pymysql, datetime, os

# Open ini file
ini_file = open('./user_config.ini', 'r')
ini_lines = ini_file.readlines()

inis = dict()

for ini_line in ini_lines:
	if ini_line[0] != '#' and ini_line != '\n' :
		(var_name, var_value) = ini_line.split("=")
		inis[var_name.rstrip(" ")] = var_value.lstrip(" ").rstrip('\n')
		
# Csonnect to db
conn = pymysql.connect(host = inis['server'], user = inis['user'], password = inis['password'], db = inis['schema'], charset = 'utf8')
curs = conn.cursor()

# Update mail table
mail_sql = "SELECT * FROM test_api_list, server_list, api_list WHERE is_running = 1 AND test_api_list.server_id = server_list.server_id AND test_api_list.api_id = api_list.api_id"
curs.execute(mail_sql)
result = curs.fetchall()

for rst in result:
	# Num index 기반이므로 table 구조가 변경되면 망한다

	# Check period
	last_time = rst[7]
	now = datetime.datetime.now()
	if last_time == None:
		last_time = datetime.datetime.strptime("1000-01-01 00:00:00", "%Y-%m-%d %H:%M:%S")
	
	test_time = last_time + datetime.timedelta(minutes = rst[5])

	if test_time < now:
		uri = rst[9]
		if uri == None:
			uri = 'http://' + rst[10] + '/'
			
		uri += rst[13]

		# Execute command
		command = inis['java_path'] + " -jar " + inis['jar_path'] + " " + uri + " " + rst[14] + " " + str(rst[0])
		os.system(command)
		
		# Update last test time
		mail_sql = "UPDATE test_api_list SET last_test_time = %s WHERE test_api_id = %s"
		curs.execute(mail_sql, (now, rst[0]))

		conn.commit()

conn.close()
