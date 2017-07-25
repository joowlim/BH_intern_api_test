import java.net.InetAddress;
import java.net.UnknownHostException;

public class Main {
	private static String url = null;
	private static String method = null;
	private static String params = "";
	private static DBManager dbManager = null;
	private static TestInfo server_api_id = null;
	public static void main(String args[]){
		
		if(args.length != 3){
			System.out.println("사용법이 올바르지 않습니다.");
			System.out.println("args length : " + args.length);
			return;
		}
		
		url = args[0];
		method = args[1];
		int testApiId = Integer.parseInt(args[2]);
		dbManager = new DBManager("API_TEST","root","root");
		if(dbManager.isConnected() == false){
			System.err.println("can not access DB. check the db server");
			return ;
		}
		TestInfo testInfo = null;
		try {
			testInfo = dbManager.getTestInfo(testApiId);

		}catch(Exception e) {
			e.printStackTrace();
		}
		
		if(testInfo == null){
			System.err.println("in main 01");
			System.err.println("server api id error! check the id");
			
			return ;
		}
		params = testInfo.params;
		RequestSender requestSender = null;
		try{
			requestSender = new RequestSender(url,method,params);

		}catch(Exception e){
			e.printStackTrace();
			System.err.println("in main 02");
			System.err.println("params err. check params");
			return;
		}

		ResultEntry resultEntry = requestSender.sendRequest();
		
		if(resultEntry == null){
			//err
			System.err.println("in main 03");
			System.err.println("result is empty. check the api url or server");
			return;
		}
		else{
			System.out.println(resultEntry.getInfo());
		}
		
		try {
			boolean isInserted = dbManager.insertResultLog(resultEntry, testInfo);
			if(isInserted == false){
				System.err.println("in main 03");
				System.err.println("insert is failed.");
				return ;
			}
			else{
				System.out.println("insert is finished");
			}
		} catch (Exception e) {
			e.printStackTrace();

		}
	}	
}
