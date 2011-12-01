var checkWaitingStatuses = function () {
  $.merge($('.p-feature-list.paused'),$('.p-feature-list.ok')).each(function(index) {
    var tr =  $(this);
    var tr_class = tr.attr('class').split(' ')[1];

    var post_params = {
      jquery:'features_module',
      action:'check',
      id:tr.attr('id')
    };

    var jqxhr = $.post(exec_url, post_params, function(data){
      if (data && data.success){
        updateFeature(tr,tr_class,data)
      } else if (data && data.error){alert(data.error);}
      else{alert(default_error_message);}
    }, "json");

  });
}

var checkAllStatuses = function () {
  $('.p-feature-list').each(function(index) {
    var tr =  $(this);
    var tr_class = tr.attr('class').split(' ')[1];

    var post_params = {
      jquery:'features_module',
      action:'check',
      id:tr.attr('id')
    };

    var jqxhr = $.post(exec_url, post_params, function(data){
      if (data && data.success){
        updateFeature(tr,tr_class,data)
      } else if (data && data.error){alert(data.error);}
      else{alert(default_error_message);}
    }, "json");
  });
}

var deleteFeature = function (id, element) {
    var post_params = {
      jquery:'features_module',
      action:'delete',
      id:id
    };

    $.post(exec_url, post_params, function(data){
      if (data && data.success) { element.fadeOut(500); }
      else if (data && data.error) { alert(data.error); }
      else { alert(default_error_message); }
    }, "json");
}

var deleteGroup = function (id, element) {
    var post_params = {
      jquery:'groups_module',
      action:'delete',
      id:id
    };

    $.post(exec_url, post_params, function(data){
      if (data && data.success) { element.fadeOut(500); }
      else if (data && data.error) { alert(data.error); }
      else { alert(default_error_message); }
    }, "json");
}

$(function() {
  $('abbr.timeago').timeago();

  $('.run-feature').bind('click',function(){
    var link = $(this);
    var tr =  $(this).parents('tr');
    var tr_class = tr.attr('class').split(' ')[1];

    var post_params = {
      jquery:'features_module',
      action:'run',
      id:tr.attr('id')
    };

    link.html('<img src="static/default/img/ajax.gif" alt="loading..."/>');

    var jqxhr = $.post(exec_url, post_params, function(data){
      if (data && data.success){
        updateFeature(tr,tr_class,data)
      } else if (data && data.error){alert(data.error);}
      else{alert(default_error_message);}
    }, "json");

    jqxhr.complete(function(){ link.html('→'); });

    return false;
  });
  
  $('.pause-feature').bind('click',function(){
    var link = $(this);
    var tr =  $(this).parents('tr');
    var tr_class = tr.attr('class').split(' ')[1];

    var post_params = {
      jquery:'features_module',
      action:'pause',
      id:tr.attr('id')
    };

    link.html('<img src="static/default/img/ajax.gif" alt="loading..."/>');

    var jqxhr = $.post(exec_url, post_params, function(data){
      if (data && data.success){
        updateFeature(tr,tr_class,data)
      } else if (data && data.error){alert(data.error);}
      else{alert(default_error_message);}
    }, "json");

    jqxhr.complete(function(){ link.html('='); });

    return false;
  });

  setInterval(function() {checkWaitingStatuses();}, 3000);
  setInterval(function() {checkAllStatuses();}, 60000);

  $('.p-feature-list-delete').bind('click',function(){
    element = $(this).parents('tr');
    id = element.attr('id');
    deleteFeature(id,element);
    return false;
  });

  $('.p-feature-group-delete a').bind('click',function(){
    if(confirm("Вы увеерны, что хотите удалить группу тестов?")) {
      element = $(this).parents('div.p-feature-group');
      id = element.attr('id');
      deleteGroup(id,element);
    }
    return false;
  });

  $('.show-feature-description').bind('click',function(){
    $(this).nextAll('.p-feature-description').toggle(); return false;
  });

  $('.p-feature-description').hide();

  $('.p-feature-group-show').bind('click',function(){
    $(this).parent().next('.p-feature-group-table').toggle(); return false;
  });
});

var updateFeature = function (tr,tr_class,data){
  tr.toggleClass(tr_class).toggleClass(data.status_description);
  tr.children('.p-feature-last_run').children('abbr').html(jQuery.timeago(data.last_run));
};
