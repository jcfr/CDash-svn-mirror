function make_bullet_chart(chart_name, min, avg, max, current, previous) {
  var chart;
  chart_name = chart_name.replace(/_/g, ' ');

  nv.addGraph(function() {
    chart = nv.models.bulletChart();
    var element_name = "#" + chart_name.replace(/ /g, '_') + "_bullet svg";
    d3.select(element_name)
      .datum({
        "ranges": [min, avg, max],
        "measures": [current],
        "markers": [previous]
        })
      .call(chart);
    return chart;
  });
}
