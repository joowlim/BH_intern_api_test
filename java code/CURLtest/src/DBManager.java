import java.net.InetAddress;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.SQLException;

import com.mysql.jdbc.Connection;
import com.mysql.jdbc.Statement;

public class DBManager {

	private Connection dbConnection = null;
	private Statement stmt = null;
	private boolean connected = false;
	public DBManager(String dbName, String id, String passwd){
		
		try{
			InetAddress local = InetAddress.getLocalHost();

			String ip = local.getHostAddress();

			if(!ip.equals("172.31.6.172")){
				ip = "52.221.182.124";
			}
			else {
				ip="localhost";
			}
			dbConnection = (Connection) DriverManager.getConnection("jdbc:mysql://"+ip+"/"+dbName,
					id, passwd);
			connected = true;
		}catch(Exception e){
			System.err.println("DB connection err");
		}
		
	}

	public boolean insertResultLog(ResultEntry entry,TestInfo idInfo){
		entry.addSlashes();
		if(idInfo == null){
			System.out.println("wrong test id : can't insert log");
			return false;
		}

		String sql = "INSERT INTO test_log(server_id,api_id,request_time,response_time,"
				+ "elapsed_time_nano,response_raw,response_code) VALUES("+idInfo.serverId+","+idInfo.apiId+",\'"+
						entry.getRequestDate()+"','"+entry.getResponseDate() +"',"+entry.getElapsed()+",\""+
						entry.getResultJson()+"\","+entry.getResponseCode()+")";
		
		try{
			stmt = (Statement) dbConnection.createStatement();
		}catch(Exception e){
			e.printStackTrace();
		}
		int res = -1;
		try{
			res = stmt.executeUpdate(sql);

		}catch(Exception e){
			e.printStackTrace();
		}
		if(res>0) return true;
		return false;
	}
	public boolean isConnected(){
		return connected;
	}
	public TestInfo getTestInfo(int testApiId) throws Exception{
		String sql = "SELECT server_id, api_id, test_params FROM test_api_list WHERE test_api_id="+testApiId;
		TestInfo ret = null;
		ResultSet rs = null;
		try {
			stmt = (Statement) dbConnection.createStatement();
		} catch (SQLException e1) {
			e1.printStackTrace();
			return null;
		}
		try {
			rs = stmt.executeQuery(sql);
		} catch (SQLException e) {
			e.printStackTrace();
			return null;
		}
		try {
			if(rs.next())
				ret = new TestInfo(rs.getInt("server_id"),rs.getInt("api_id"),rs.getString("test_params"));
			else {
				throw new Exception();
			}
			rs.close();
		} catch (SQLException e) {
			e.printStackTrace();
			return null;
		}
		return ret;
	}


}
