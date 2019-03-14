<html>  
<head>  

<meta charset="utf-8"/>

<script>  
function setInfo() {
    var username = document.getElementById("uname");
    if (username.value == "" || username.value == null) username.value = "请输入";
}
function clearInfo() {
    var username = document.getElementById("uname").value = "";
}

</script>  
</head>  
<body onload="setInfo()">  

<center>
<img src="/figs/train.jpeg"  />
<H1 id="h1"> 欢迎访问12306网站</H1>
   <center> 
     <tbody>
      <tr height="25"> 
       <td width="570px"> </td> 
       <td> </td> 
      </tr> 
      <tr> 
       <td> </td> 
       <td>
        <table cellpadding="5"> 
         <tbody>
          <tr>
           <!-- <td>用户名:</td>
           <td colspan="3"><input type="text" name="username" id="uname" value="" onfocus="clearInfo()" onblur="setInfo()" /></td> 
          </tr> 
          <tr>
           <td>密 码:</td>
           <td colspan="3"><input type="password" id="passwd" value="" onfocus="clearInfo()" onblur="setInfo()" /> </td>
          </tr>  -->
          <tr></tr>
         </tbody>
        </table>
        <table cellspacing="30"> 
         <tbody>
          <tr>
            <td><input type="button" value = "用户注册" onclick="location.href='./sign/sign-up.php'"></td> 
            <td><input type="button" value = "用户登录" onclick="location.href='./sign/user-sign-in.php'"></td>  
            <td><input type="button" value = "管理员登录" onclick="location.href='./sign/admi-sign-in.php'"></td>  
          </tr>
         </tbody>
        </table></td>
      </tr> 
     </tbody>
    </table>   
   </center>  

  </form>
</body>  
</html>
