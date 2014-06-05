function make_bullet_chart(chart_name, element_name, min, avg, max, current,
                           previous, chart_height) {
  // note that chart_height is just for the chart itself (not the labels)
  var chart;
  nv.addGraph(function() {
    chart = nv.models.bulletChart()
    .options({
      margin: {top: 0, right: 5, bottom: 5, left: 5},
      height: chart_height
    });
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
