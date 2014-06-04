function make_bullet_chart(chart_name, element_name, min, avg, max, current, previous) {
  var chart;
  nv.addGraph(function() {
    chart = nv.models.bulletChart();
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
