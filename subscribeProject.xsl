<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version='1.0'>

   <xsl:include href="footer.xsl"/>
    
    <xsl:output method="html"/>
    <xsl:template match="/">
      <html>
       <head>
       <title><xsl:value-of select="cdash/title"/></title>
        <meta name="robots" content="noindex,nofollow" />
         <link rel="StyleSheet" type="text/css">
         <xsl:attribute name="href"><xsl:value-of select="cdash/cssfile"/></xsl:attribute>
         </link>
        
        <!-- Include CDash Menu Stylesheet -->    
        <link rel="stylesheet" href="javascript/cdashmenu.css" type="text/css" media="screen" charset="utf-8" />
  
        <!-- Include the rounding css -->
        <script src="javascript/rounded.js"></script>

       </head>
       <body bgcolor="#ffffff">

<table border="0" cellpadding="0" cellspacing="2" width="100%">
<tr>
<td align="center"><a href="index.php"><img alt="Logo/Homepage link" height="100" src="images/cdash.gif" border="0"/></a>
</td>
<td valign="bottom" width="100%">
<div style="margin: 0pt auto; background-color: #6699cc;"  class="rounded">  
<font color="#ffffff"><h2>CDash - Subscribe to project <xsl:value-of select="cdash/project/name"/></h2>
<h3>Subscribing to a project</h3></font>
<br/></div>
</td>
</tr>
<tr>
<td></td><td>
<!-- Menu -->
<ul id="Nav" class="nav">
  <li>
     <a href="user.php">Back</a>
  </li>
</ul>
</td>
</tr>
</table>

<br/>

<xsl:if test="string-length(cdash/warning)>0">
<xsl:value-of select="cdash/warning"/>
</xsl:if>

<form name="form1" enctype="multipart/form-data" method="post" action="">
<table width="100%"  border="0">
  <tr>
    <td></td>
    <td></td>
  </tr>
  <tr>
    <td width="98"></td>
    <td bgcolor="#CCCCCC"><strong>Select your role in this project</strong></td>
  </tr>
   <tr>
    <td></td>
    <td bgcolor="#EEEEEE"><input type="radio" name="role" value="0" checked="true">
				<xsl:if test="/cdash/role=0">
				<xsl:attribute name="checked">true</xsl:attribute>
				</xsl:if>
				</input>
				 Normal user <i>(you are working on or using this toolkit)</i></td>
  </tr>
		 <tr>
    <td></td>
    <td bgcolor="#EEEEEE"><input type="radio" name="role" value="1">
					<xsl:if test="/cdash/role=1">
				<xsl:attribute name="checked">true</xsl:attribute>
				</xsl:if>
				</input>
				 Dashboard maintainer <i>(you are responsable of machines that are submitting builds for this project)</i></td>
  </tr>
		 <tr>
    <td></td>
    <td bgcolor="#FFFFFF"></td>
  </tr>	
		<tr>
    <td width="98"></td>
    <td bgcolor="#CCCCCC"><strong>CVS/SVN login</strong></td>
  </tr>
   <tr>
    <td></td>
    <td bgcolor="#EEEEEE">Login: <input type="text" name="cvslogin" size="30">
				 <xsl:attribute name="value">
       <xsl:value-of select="cdash/cvslogin"/>
				 </xsl:attribute>
					</input>
				 <i>(your login is used to send you an email when the dashboard breaks)</i></td>
  </tr>
		<tr>
    <td></td>
    <td bgcolor="#FFFFFF"></td>
  </tr>	
		<tr>
    <td></td>
				<td bgcolor="#FFFFFF">
				<xsl:if test="/cdash/edit=1">
				  <input type="submit" name="updatesubscription" value="Update Subscription"/>
				 <input type="submit" name="unsubscribe" value="Unsubscribe"/>
				  </xsl:if>
				  <xsl:if test="/cdash/edit=0">
      <input type="submit" name="subscribe" value="Subscribe"/>
				</xsl:if>
				</td>
  </tr>	
</table>
</form>
<br/>

<br/>

<!-- Rounding script -->
<script type="text/javascript">
  Rounded('rounded', 15, 15,0,0);
</script>

<!-- FOOTER -->
<br/>
<xsl:call-template name="footer"/>
        </body>
      </html>
    </xsl:template>
</xsl:stylesheet>
