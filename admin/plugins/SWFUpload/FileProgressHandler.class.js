/**
 * FileProgress object
 * @param file object
 * @param containerID string id of the main container
 * @param targetID string
 */
function FileProgressHandler(file, containerID, targetID) {
    
    this.jElement = $('#' + containerID + ' .'+targetID);
    var html = '';
    html += '<div class="progressContainer">';
    html += '<a class="progressCancel" href="#" style="visibility: hidden;"> </a>';
    html += '<div class="progressTitle"><b>Uploaden</b> <span class="progressFiles2Go"></span></div>';
    html += '<div class="progressName"></div>';
    html += '<div class="progressStatus"></div>';
    html += '<div class="progressBar"></div>';
    html += '</div>';
    
    this.jElement.html(html);
}

/**
 * set progress bar
 * @param percentage int
 */
FileProgressHandler.prototype.setProgressBar = function(percentage){
    this.jElement.find('.progressBar').css('width', percentage+'%');
}

/**
 * set progress name
 * @param name string
 */
FileProgressHandler.prototype.setProgressName = function(name){
    this.jElement.find('.progressName').html(name);
}

/**
 * set progress status
 * @param status string
 */
FileProgressHandler.prototype.setProgressStatus = function(status){
    this.jElement.find('.progressStatus').html(status);
}

/**
 * hide progress container
 */
FileProgressHandler.prototype.hide = function(){
    var oSelf = this;
    setTimeout(function () {
        oSelf.jElement.hide(500, function(){
            oSelf.reset();
        });
    }, 4500); 
}

/**
 * reset progress container
 */
FileProgressHandler.prototype.reset = function(){
    this.jElement.html('');
    this.jElement.show();
}

/**
 * set progress files2Go
 * @param files2Go string
 */
FileProgressHandler.prototype.setProgressFiles2Go = function(files2Go){
    this.jElement.find('.progressFiles2Go').html(files2Go);
}