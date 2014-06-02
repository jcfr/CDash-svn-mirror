<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version='1.0'>

   <xsl:include href="header.xsl"/>
   <xsl:include href="footer.xsl"/>

   <xsl:include href="local/header.xsl"/>
   <xsl:include href="local/footer.xsl"/>

   <xsl:output method="xml" indent="yes"  doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"
   doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
    <xsl:template match="/">
      <html>
       <head>
       <title><xsl:value-of select="cdash/title"/></title>
        <meta name="robots" content="noindex,nofollow" />
         <link rel="StyleSheet" type="text/css">
         <xsl:attribute name="href"><xsl:value-of select="cdash/cssfile"/></xsl:attribute>
         </link>
         <xsl:call-template name="headscripts"/>

         <!-- Include JavaScript -->
         <script src="javascript/cdashBuildGraph.js" type="text/javascript" charset="utf-8"></script>
         <script src="javascript/cdashAddNote.js" type="text/javascript" charset="utf-8"></script>
       </head>
       <body bgcolor="#ffffff">

<table>
  <tr>
    <th>Build Group</th>
    <th>Configure Warnings</th>
    <th>Configure Errors</th>
    <th>Build Warnings</th>
    <th>Build Errors</th>
    <th>Failing Tests</th>
  </tr>

  <xsl:for-each select='/cdash/group'>
    <tr>
      <td><xsl:value-of select="name"/></td>
      <td><xsl:value-of select="configure_warnings"/></td>
      <td><xsl:value-of select="configure_errors"/></td>
      <td><xsl:value-of select="build_warnings"/></td>
      <td><xsl:value-of select="build_errors"/></td>
      <td><xsl:value-of select="failing_tests"/></td>
    </tr>
  </xsl:for-each>
</table>

<!-- FOOTER -->
<br/>
<xsl:choose>
<xsl:when test="/cdash/uselocaldirectory=1">
  <xsl:call-template name="footer_local"/>
</xsl:when>
<xsl:otherwise>
  <xsl:call-template name="footer"/>
</xsl:otherwise>
</xsl:choose>
        </body>
      </html>
    </xsl:template>
</xsl:stylesheet>
