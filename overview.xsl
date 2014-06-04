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
        <script src="javascript/bulletchart.js" type="text/javascript" charset="utf-8"></script>

        <!-- Generate line charts -->
        <script type="text/javascript">
          <xsl:for-each select='/cdash/measurement'>
            <xsl:variable name="measurement_name" select="name"/>
            <xsl:variable name="measurement_nice_name" select="nice_name"/>
            <xsl:for-each select='group'>
              var <xsl:value-of select="group_name"/>_<xsl:value-of select="$measurement_name"/> =
                <xsl:value-of select="chart"/>;
              make_line_chart("<xsl:value-of select="group_name"/>" + " " + "<xsl:value-of select="$measurement_nice_name"/>",
                              <xsl:value-of select="group_name"/>_<xsl:value-of select="$measurement_name"/>);
            </xsl:for-each>
          </xsl:for-each>
          <xsl:if test="/cdash/coverage">
            var core_coverage_chart = <xsl:value-of select="/cdash/coverage/chart"/>;
            make_line_chart("core coverage", core_coverage_chart);
            make_bullet_chart("core coverage",
              <xsl:value-of select="/cdash/coverage/min"/>,
              <xsl:value-of select="/cdash/coverage/avg"/>,
              <xsl:value-of select="/cdash/coverage/max"/>,
              <xsl:value-of select="/cdash/coverage/value"/>,
              <xsl:value-of select="/cdash/coverage/previous"/>);
          </xsl:if>
          <xsl:if test="/cdash/non_core_coverage">
            var non_core_coverage_chart = <xsl:value-of select="/cdash/non_core_coverage/chart"/>;
            make_line_chart("non core coverage", non_core_coverage_chart);
            make_bullet_chart("non core coverage",
              <xsl:value-of select="/cdash/non_core_coverage/min"/>,
              <xsl:value-of select="/cdash/non_core_coverage/avg"/>,
              <xsl:value-of select="/cdash/non_core_coverage/max"/>,
              <xsl:value-of select="/cdash/non_core_coverage/value"/>,
              <xsl:value-of select="/cdash/non_core_coverage/previous"/>);
          </xsl:if>
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

        <table class="table-bordered table-responsive table-condensed container-fluid">
          <tr class="row">
              <th class="col-md-1"> </th>
                <xsl:for-each select='/cdash/group'>
                  <th class="col-md-2" colspan="2">
                    <xsl:value-of select="name"/>
                  </th>
                </xsl:for-each>
          </tr>

          <xsl:for-each select='/cdash/measurement'>
            <xsl:variable name="measurement_name" select="name"/>
            <tr class="row">
              <td class="col-md-1">
                <b><xsl:value-of select="nice_name"/></b>
              </td>
              <xsl:for-each select='group'>
                <td class="col-md-1">
                  <xsl:value-of select="value"/>
                </td>
                <td class="col-md-1" id="{group_name}_{$measurement_name}_chart">
                  <svg width="100%" height="100%"></svg>
                </td>
              </xsl:for-each>
            </tr>
          </xsl:for-each>

          <xsl:if test="/cdash/coverage">
            <tr class="row">
              <td class="col-md-7" colspan="7"></td>
            </tr>
            <tr class="row">
              <td class="col-md-1"><b>Coverage</b></td>
              <td class="col-md-1">
                <xsl:value-of select="/cdash/coverage/value"/>%
              </td>
              <td id="core_coverage_chart" class="col-md-1">
                <svg width="100%" height="100%"></svg>
              </td>
              <td id="core_coverage_bullet" class="col-md-4" colspan="4">
                <svg></svg>
              </td>
            </tr>
              <xsl:if test="/cdash/non_core_coverage">
                <tr class="row">
                  <td class="col-md-1"><b>Non-core coverage</b></td>
                  <td class="col-md-1">
                    <xsl:value-of select="/cdash/non_core_coverage/value"/>%
                  </td>
                  <td id="non_core_coverage_chart" class="col-md-1">
                    <svg width="100%" height="100%"></svg>
                  </td>
                  <td id="non_core_coverage_bullet" class="col-md-4" colspan="4">
                    <svg width="100%" height="100%"></svg>
                  </td>
                </tr>
              </xsl:if>
          </xsl:if>
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
