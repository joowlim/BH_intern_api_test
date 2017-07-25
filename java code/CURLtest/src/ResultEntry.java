
public class ResultEntry {
	private  String requestDate;
	private String responseDate;
	private int responseCode;
	private long elapsed;
	private String resultJson;
	private long resultCode;
	private long numOfMessagePatterns;
	private boolean isError;
	

	public ResultEntry(String req,String res,long elp,String ret,int responseCode){
		requestDate = req;
		responseDate = res;
		elapsed = elp;
		resultJson = ret;
		this.responseCode = responseCode;
		//JsonObject jsonObject = new JsonParser().parse(resultJson).getAsJsonObject();
		//resultCode = jsonObject.get("result").getAsLong();
		
		//numOfMessagePatterns = jsonObject.get("messagePatterns").getAsJsonArray().size();
		isError = false;
	}
	public ResultEntry(boolean err){
		isError = true;
	}
	public String getInfo(){
		String ret = String.format("requestDate:%s\nresponseDate:%s\nelapsed:%d(nanosec)\nresponse code:%d\n",
				requestDate,responseDate,elapsed,responseCode);
		String raw = resultJson;
		return resultJson + "\n" + ret;

	}
	
	
	public void addSlashes() {
		resultJson = resultJson.replace("\\", "\\\\").replace("'", "\\\\'").replace("\"", "\\\"");
    }
	
	
	public String getRequestDate() {
		return requestDate;
	}
	public void setRequestDate(String requestDate) {
		this.requestDate = requestDate;
	}
	public String getResponseDate() {
		return responseDate;
	}
	public void setResponseDate(String responseDate) {
		this.responseDate = responseDate;
	}
	public int getResponseCode() {
		return responseCode;
	}
	public void setResponseCode(int responseCode) {
		this.responseCode = responseCode;
	}
	public long getElapsed() {
		return elapsed;
	}
	public void setElapsed(long elapsed) {
		this.elapsed = elapsed;
	}
	public String getResultJson() {
		return resultJson;
	}
	public void setResultJson(String resultJson) {
		this.resultJson = resultJson;
	}
	public long getResultCode() {
		return resultCode;
	}
	public void setResultCode(long resultCode) {
		this.resultCode = resultCode;
	}
	public long getNumOfMessagePatterns() {
		return numOfMessagePatterns;
	}
	public void setNumOfMessagePatterns(long numOfMessagePatterns) {
		this.numOfMessagePatterns = numOfMessagePatterns;
	}
	public boolean isError() {
		return isError;
	}
	public void setError(boolean isError) {
		this.isError = isError;
	}

	 
}
