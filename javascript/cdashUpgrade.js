/* Helper function */
function sendAjax(divname,ajaxurl,text,nextstep)
{
  $.ajax({
   type: "GET",
   url: ajaxurl,
   success: function(msg){
    
    $(divname).html(msg);
    
    var index = msg.indexOf(':');
    var prefix = '';
    var error = '';
    if(index>0)
      {
      prefix = msg.substr(0,index);
      error = msg.substr(index+1);
      }
    
    if(prefix != "ERROR" && prefix != "WARNING")
      {
      $(divname).html(text+": <img src=\"images/check.gif\""); 
      nextstep();
      }
    else if(prefix == "WARNING")
      {
      $(divname).html(text+": "+error+" <img src=\"images/check.gif\"");
      nextstep();
      }
    else
      {
      $(divname).html("An error as occured");
      }
   }
   });
}

function upgrade_tables()
{
  var text = "Upgrading tables";
  $("#Upgrade-Tables-Status").html("<img src=\"images/loading.gif\"/> "+text+"...");
  sendAjax("#Upgrade-Tables-Status","backwardCompatibilityTools.php?upgrade-tables=1",text,upgrade_0_8);  
}

function upgrade_0_8()
{
  var text = "Applying 0.8 patches";
  $("#Upgrade-0-8-Status").html("<img src=\"images/loading.gif\"/> "+text+"...");
  sendAjax("#Upgrade-0-8-Status","backwardCompatibilityTools.php?upgrade-0-8=1",text,upgrade_1_0);  
}

function upgrade_1_0()
{
  var text = "Applying 1.0 patches";
  $("#Upgrade-1-0-Status").html("<img src=\"images/loading.gif\"/> "+text+"...");
  sendAjax("#Upgrade-1-0-Status","backwardCompatibilityTools.php?upgrade-1-0=1",text,done);  
}


// empty function needed
function done()
{
  $("#DoneStatus").html("<b>CDash Upgrade Successful.</b>");
}

$(document).ready(function() {  
  // Trigger the first ajax function
  upgrade_tables();
});