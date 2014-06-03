<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version='1.0'>

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

          <!-- Include static css -->
          <link href="nv.d3.css" rel="stylesheet" type="text/css"/>
          <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css"/>

          <!-- Include JavaScript -->
          <script src="javascript/cdashBuildGraph.js" type="text/javascript" charset="utf-8"></script>
          <script src="javascript/cdashAddNote.js" type="text/javascript" charset="utf-8"></script>
          <script src="javascript/d3.min.js" type="text/javascript" charset="utf-8"></script>
          <script src="javascript/nv.d3.min.js" type="text/javascript" charset="utf-8"></script>
          <script src="javascript/linechart.js" type="text/javascript" charset="utf-8"></script>

          <!-- Generate line charts -->
          <script type="text/javascript">
            <xsl:for-each select='/cdash/group'>
              var <xsl:value-of select="name"/>_configure_warnings =
                <xsl:value-of select="chart_configure_warnings"/>;
              make_line_chart("<xsl:value-of select="name"/> configure warnings",
                              <xsl:value-of select="name"/>_configure_warnings);
              var <xsl:value-of select="name"/>_configure_errors =
                <xsl:value-of select="chart_configure_errors"/>;
              make_line_chart("<xsl:value-of select="name"/> configure errors",
                              <xsl:value-of select="name"/>_configure_errors);
              var <xsl:value-of select="name"/>_build_warnings =
                <xsl:value-of select="chart_build_warnings"/>;
              make_line_chart("<xsl:value-of select="name"/> build warnings",
                              <xsl:value-of select="name"/>_build_warnings);
              var <xsl:value-of select="name"/>_build_errors =
                <xsl:value-of select="chart_build_errors"/>;
              make_line_chart("<xsl:value-of select="name"/> build errors",
                              <xsl:value-of select="name"/>_build_errors);
              var <xsl:value-of select="name"/>_failing_tests =
                <xsl:value-of select="chart_failing_tests"/>;
              make_line_chart("<xsl:value-of select="name"/> failing tests",
                              <xsl:value-of select="name"/>_failing_tests);
            </xsl:for-each>
          </script>
        </head>

        <body bgcolor="#ffffff">

        <xsl:choose>
        <xsl:when test="/cdash/uselocaldirectory=1">
          <xsl:call-template name="header_local"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:call-template name="header"/>
        </xsl:otherwise>
        </xsl:choose>


<table class="table-bordered table-responsive table-condensed">
  <tr class="row">
    <th class="col-md-2">Build Group</th>
    <th class="col-md-2" colspan="2">Configure Warnings</th>
    <th class="col-md-2" colspan="2">Configure Errors</th>
    <th class="col-md-2" colspan="2">Build Warnings</th>
    <th class="col-md-2" colspan="2">Build Errors</th>
    <th class="col-md-2" colspan="2">Failing Tests</th>
  </tr>

  <xsl:for-each select='/cdash/group'>
    <tr class="row">
      <td class="col-md-2">
        <xsl:value-of select="name"/>
      </td>
      <td class="col-md-1" id="{name}_configure_warnings">
        <xsl:value-of select="configure_warnings"/>
      </td>
      <td class="col-md-1" id="{name}_configure_warnings_chart">
        <svg width="100%" height="100%"></svg>
      </td>
      <td class="col-md-1" id="{name}_configure_errors">
        <xsl:value-of select="configure_errors"/>
      </td>
      <td class="col-md-1" id="{name}_configure_errors_chart">
        <svg width="100%" height="100%"></svg>
      </td>
      <td class="col-md-1" id="{name}_build_warnings">
        <xsl:value-of select="build_warnings"/>
      </td>
      <td class="col-md-1" id="{name}_build_warnings_chart">
        <svg width="100%" height="100%"></svg>
      </td>
      <td class="col-md-1" id="{name}_build_errors">
        <xsl:value-of select="build_errors"/>
      </td>
      <td class="col-md-1" id="{name}_build_errors_chart">
        <svg width="100%" height="100%"></svg>
      </td>
      <td class="col-md-1" id="{name}_failing_tests">
        <xsl:value-of select="failing_tests"/>
      </td>
      <td class="col-md-1" id="{name}_failing_tests_chart">
        <svg width="100%" height="100%"></svg>
      </td>
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
