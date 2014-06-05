function make_line_chart(chart_name, element_name, input_data) {
  var chart;

  nv.addGraph(function() {
    chart = nv.models.lineChart()
    .options({
      showLegend: false,
      showXAxis: false,
      showYAxis: false,
      margin: {top: 2, right: 2, bottom: 2, left: 2},
    });

    var chart_data = [{
      values: input_data,
      key: chart_name,
      color: "#ff7f0e",
      area: false
    }];
    d3.select(element_name)
      .datum(chart_data)
      .call(chart);

    nv.utils.windowResize(chart.update);
    return chart;
  });
}
