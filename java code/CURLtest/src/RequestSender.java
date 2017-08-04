import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.io.UnsupportedEncodingException;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.util.Calendar;
import java.util.HashMap;
import java.util.Map;

import com.google.gson.Gson;

public class RequestSender {

	private static String DATE_FORMAT = "dd/MM/yyyy hh:mm:ss.SSS";
	
	private URL requestURL;
	private String method;
	private ResultEntry resultEntry = null;
	private Gson gson = new Gson();
	private HttpURLConnection connection = null;
	private String formattedParams="";
	
	public RequestSender(String url,String method, String params){
		this.method = method;
		if(params.equals("")){
			try{
				requestURL = new URL(url);
				
			}
			catch(Exception e){
				e.printStackTrace();
			}
			return;
		}
		
		
		Map<String,Object> map = new HashMap<String, Object>();
		try{
	    	map = (Map<String,Object>) gson.fromJson(params, map.getClass());

		}catch(Exception e){
			System.err.println("params is not valid json string");
			throw e;
		}
    	
    	StringBuffer tempParams = new StringBuffer("");
    	for(String key : map.keySet()){
    		String eachParams = key + "=" + URLEncoder.encode(map.get(key)+"") + "&";
    		tempParams.append(eachParams);
    	}
    	formattedParams = tempParams.toString();
		if(method.equals("GET")){
			try{
				requestURL = new URL(String.format("%s?%s",url,formattedParams));
			}
			catch(Exception e){
				e.printStackTrace();
			}
		}
		
		else if(method.equals("POST")){
			try{
				requestURL = new URL(url);
			}catch(Exception e){
				e.printStackTrace();
			}			
		}
		else{
			System.err.println("Wrong method");
		}
	}
	public ResultEntry sendRequest(){

		if(method.equals("GET")){
			return sendGetRequest();
		}
		else if(method.equals("POST")){
			return sendPostRequest();
		}
		else{
			System.err.println("method is wrong");
			return null;
		}
	}
	
	private String milliToDateFormat(long milis,String dateFormat){
		java.text.SimpleDateFormat formatter = new java.text.SimpleDateFormat(dateFormat);
        Calendar calendar = Calendar.getInstance();
        calendar.setTimeInMillis(milis);
        return formatter.format(calendar.getTime());
	}
	
	private ResultEntry sendGetRequest(){
		int responseCode = -1;
		long requestTime = 0,responseTime = 0;
		long elapsed = 0;
		long requestTimeMillis = 0, responseTimeMillis = 0;
		try{
			requestTimeMillis = System.currentTimeMillis();
			requestTime = System.nanoTime();
			connection = (HttpURLConnection) requestURL.openConnection();
			connection.setConnectTimeout(5000);
			connection.setReadTimeout(5000);
			
		}catch(Exception e){
			e.printStackTrace();
			return null;
		}
		
		try{
			responseCode = connection.getResponseCode();
			if(responseCode != 200){
				connection.disconnect();
				connection = null;
				System.out.println("error code is not 200");
				System.out.println("err code is "+responseCode);
				return null;
			}
		}catch(Exception e){
			e.printStackTrace();
			return null;
		}
		
		StringBuffer sb = new StringBuffer();
		try{
			InputStream bis = connection.getInputStream();
			BufferedReader br = new BufferedReader(new InputStreamReader(bis,StandardCharsets.UTF_8));
			
			String inputLine = "";
			while((inputLine = br.readLine()) != null){
				sb.append(inputLine + "\n");
			}
			responseTime = System.nanoTime();
			responseTimeMillis = System.currentTimeMillis();
			elapsed = responseTime - requestTime;
			
		}catch(Exception e){
			e.printStackTrace();
			return null;
		}
		try{
			connection.disconnect();
			connection = null;
		}catch(Exception e){
			e.printStackTrace();
			return null;
		}
		
		ResultEntry ret = new ResultEntry(milliToDateFormat(requestTimeMillis,DATE_FORMAT),
				milliToDateFormat(responseTimeMillis,DATE_FORMAT),elapsed,sb.toString(),responseCode);
		return ret;
	}
	
	private ResultEntry sendPostRequest(){
		int responseCode = -1;
		long requestTime = 0,responseTime = 0;
		long elapsed = 0;
		long requestTimeMillis = 0, responseTimeMillis = 0;
		try{
			requestTimeMillis = System.currentTimeMillis();
			requestTime = System.nanoTime();
			connection = (HttpURLConnection) requestURL.openConnection();
			
        	connection.setRequestProperty("Content-Type", "application/x-www-form-urlencoded");
            connection.setDoInput(true);
            String param = formattedParams;
            connection.setDoOutput(true);
            OutputStream out_stream = connection.getOutputStream();
            out_stream.write( param.getBytes("UTF-8") );
            out_stream.flush();
            out_stream.close();
            
		}catch(Exception e){
			e.printStackTrace();
			return null;
		}
		try{
			responseCode = connection.getResponseCode();
			if(responseCode != 200){
				connection.disconnect();
				connection = null;
				System.err.println("error code is not 200");
				return null;
			}
		}catch(Exception e){
			e.printStackTrace();
			return null;
		}
		
		StringBuffer sb = new StringBuffer();
		try{

			InputStream bis = connection.getInputStream();
			BufferedReader br = new BufferedReader(new InputStreamReader(bis,StandardCharsets.UTF_8));
			
			String inputLine = "";
			while((inputLine = br.readLine()) != null){
				sb.append(inputLine + "\n");
			}
			responseTime = System.nanoTime();
			responseTimeMillis = System.currentTimeMillis();
			elapsed = responseTime - requestTime;
			
		}catch(Exception e){
			e.printStackTrace();
			return null;
		}
		try{
			connection.disconnect();
			connection = null;
		}catch(Exception e){
			e.printStackTrace();
			return null;
		}
		
		ResultEntry ret = null;
		ret = new ResultEntry(milliToDateFormat(requestTimeMillis,DATE_FORMAT),
			milliToDateFormat(responseTimeMillis,DATE_FORMAT),elapsed,sb.toString(),responseCode);

		return ret;
	}
	
}
