// assumes the labels of the sortable child elements
// are stored within <p> tags
function get_sorted_elements(parent) {
  var positions = [];
  $(parent).children().each(function() {
    var pos = {};
    pos.buildgroupid = $(this).attr('id');
    pos.position = $(this).index() + 1;
    positions.push(pos);
  });
  return positions;
}
