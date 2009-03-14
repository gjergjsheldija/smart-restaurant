  $.growl.settings.displayTimeout = 1500;
  $.growl.settings.noticeTemplate = ''
    + '<div>'
    + '<div style="float: right; background-image: url(../images/dm_top.png); position: relative; width: 259px; height: 16px; margin: 0pt;"></div>'
    + '<div style="float: right; background-image: url(../images/dm_repeat.png); position: relative; display: block; color: #ffffff; font-family: Arial; font-size: 12px; line-height: 14px; width: 259px; margin: 0pt;">' 
    + '  <img style="margin: 14px; margin-top: 0px; float: left;" src="%image%" />'
    + '  <h3 style="margin: 0pt; margin-left: 77px; padding-bottom: 10px; font-size: 13px;">%title%</h3>'
    + '  <p style="margin: 0pt 14px; margin-left: 77px; font-size: 12px;">%message%</p>'
    + '</div>'
    + '<div style="float: right; background-image: url(../images/dm_bottom.png); position: relative; width: 259px; height: 16px; margin-bottom: 10px;"></div>'
    + '</div>';
  $.growl.settings.noticeCss = {
    position: 'relative',
    opacity: .85
  };
  $.growl.settings.dockCss = {
    position: 'absolute',
    bottom: '30px',
    right: '10px',
    width: '300px'
  };
