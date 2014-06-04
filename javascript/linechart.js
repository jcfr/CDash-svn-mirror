function make_line_chart(chart_name, input_data) {
  var chart;
  chart_name = chart_name.replace(/_/g, ' ');

  nv.addGraph(function() {
    chart = nv.models.lineChart()
    .options({
      showLegend: false,
      showXAxis: false,
      showYAxis: false,
      margin: {top: 0, right: 0, bottom: 0, left: 0}
    });

    var chart_data = [{
      values: input_data,
      key: chart_name,
      color: "#ff7f0e",
      area: false
    }];
    var element_name = "#" + chart_name.replace(/ /g, '_') + "_chart svg";
    d3.select(element_name)
      .datum(chart_data)
      .call(chart);

    nv.utils.windowResize(chart.update);
    return chart;
  });
}
