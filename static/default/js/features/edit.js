$(function() {
  var editor = ace.edit("description");
  var textarea = $('textarea[name="description"]').hide();
  var RubyMode = require("ace/mode/ruby").Mode;
  editor.getSession().setMode(new RubyMode());
  editor.setTheme("ace/theme/twilight");
  editor.getSession().setValue(textarea.val());
  editor.getSession().setUseWrapMode(true);
  editor.getSession().setUseSoftTabs(false);
  editor.getSession().setTabSize(2);
  editor.getSession().on('change', function(){
    textarea.val(editor.getSession().getValue());
  });
});
