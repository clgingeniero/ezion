ajaxlogin = Class.create();
ajaxlogin.prototype = {
    initialize: function(formId){
	
        /* Custom position values */
        this.horizontalCorrection = parseInt(ajaxloginForm.horizontalCorrection);
        this.verticalCorrection = parseInt(ajaxloginForm.verticalCorrection); 
        
        this.form       = $(formId);
        this.formId = formId;
        if (!this.form) {
            return;
        }
        
        this.bindElements();
        this.prepareTopLinks();
        
    },
    
    bindElements:function (){
        var elements = Form.getElements(this.form);
        for (var row in elements) {
            if (elements[row].id) {
                this.writeDefaults(elements[row]);
			
                Event.observe(elements[row],'focus',				
                    function() {				
                        if(this.value == ajaxlogin.loginValue || this.value == ajaxlogin.passwordValue) {
                            this.value = '';
                        }
                    }
                    );
                Event.observe(elements[row],'blur',function() {				
                    if(this.value.blank() && this.id == 'do-mini-login') {					
                        this.value = ajaxlogin.loginValue;					
                    }					
                    else if(this.value.blank() && this.id == 'do-mini-password') {					
                        this.value = ajaxlogin.passwordValue;					
                    }					
                });
            }
        }
    },
	
	
    writeDefaults: function(elem) {
        if(elem.id && elem.id == 'do-mini-login') {
            ajaxlogin.loginValue = elem.value;
        }
		
        if(elem.id && elem.id == 'do-mini-password') {
            ajaxlogin.passwordValue = elem.value;
        }
    },  

    computeBlockPosition: function(id) {

        var a = $(ajaxlogin.loginLink);
        var offsets = a.viewportOffset();
        var dimensions = a.getDimensions();
        var formDimensions = $(id).getDimensions();        
        var scrollOffsets = document.viewport.getScrollOffsets();
 
        topOffset = parseInt(offsets[1]) + parseInt(dimensions.height)  + parseInt(this.verticalCorrection) + parseInt(scrollOffsets.top);
        leftOffset = parseInt(offsets[0]) - parseInt(formDimensions.width) + parseInt(dimensions.width) + parseInt(this.horizontalCorrection) + parseInt(scrollOffsets.left);


        if(ajaxloginForm.computePosition) {
            $(id).setStyle({
                position:'absolute',
                zIndex: 100,
                top:topOffset+'px',
                left:leftOffset+'px'

            });
        }               

        if(ajaxloginForm.hideForm) {
            a.href = "javascript:void(0)";
        }

        Event.observe(a,'click',function() {
            Effect.toggle(id+'_ul');
            Effect.toggle(id, 'appear', {
                afterFinish: function() {
                    ajaxloginForm.getClass().reset()
                }
            });
        }.bind(this));


},
   

prepareTopLinks: function() {

    $$("UL.links LI A").each(function(a) {

        if(a.href.search(/customer\/account\/login\//ig) != -1) {               
            ajaxlogin.loginLink = a.id = 'ajax_login_link';
            a.up('li').addClassName('ajaxlogin-logout-container');
        }
        if(a.href.search(/\/wishlist\//ig) != -1) {
            a.addClassName('ajaxlogin-wishlist-container');
        }
        if(a.href.search(/\/checkout\/cart/ig) != -1) {
            a.addClassName('ajaxlogin-toplink_cart');
        }
        if(a.href.search(/customer\/account\/create\//ig) != -1) {               
            ajaxlogin.registerLink = a.id = 'ajax_login_register';
            a.up('li').addClassName('ajaxlogin-register-container');
        }

    });
 
},

reset: function() {
         
    $$('#' + this.formId + ' .validation-advice').invoke('setStyle',{
        display:'none'
    });
    this.form.reset();
        
},

validateForm: function() {
    dataForm = new Validation(this.formId);
    if(dataForm && dataForm.validate()) {
        if(this.checkAjaxStatus()) {
            this.prepareParams();
            this.sendRequest();
        }
        else {
            return true;
        }
    }
    return false; 
},

prepareParams: function() {
	 
    this.preCheckUrl =  this.form.action.replace(/customer/igm,'fastlogin');
    this.userEmail =    this.form.getInputs('text','login[username]')[0].value;
    this.userPassword = this.form.getInputs('password','login[password]')[0].value;
      
},

sendRequest: function() {

    this.toggleLoader(1);

    new Ajax.Request(this.preCheckUrl, {

        method: 'post',
        parameters: 'userEmail='+this.userEmail+"&userPassword="+this.userPassword,

        onSuccess: function(avObj) {			
				 
            if(avObj.responseText.isJSON() == false) {
                ajaxloginForm.getClass().toggleLoader(2);
                $('do-err-messages').innerHTML = avObj.responseText;                       
                $$('#ajax_login_wrapper .messages').invoke('setStyle',{
                    display:'block'
                });						 
                return;                   
            }
 
            jsonObj = avObj.responseText.evalJSON(); 
                     

            if(jsonObj.action == 'redirect') {

                obj =  ajaxloginForm.getClass();

                try {                               
                    obj.setTopLinkHtml('ajaxlogin-wishlist-container',jsonObj.wishlistLink);
                } catch(err) {}

                try {
                    obj.setTopLinkHtml('top-link-cart',jsonObj.cartLink);
                } catch(err) {}

                try {
                    obj.setTopLinkHtml('ajaxlogin-logout-container',jsonObj.logoutLink);
                } catch(err) {}
                
                try {                    
                    $$('.ajaxlogin-register-container').each(function(reg) {                        
                        $(reg).remove();                        
                    });
                } catch(e) {}
                            
                $('ajax_login_wrapper').remove();
                         
                if(ajaxloginForm.cartSidebar) {
                    try {
                        obj.setTopLinkHtml('block-cart',jsonObj.sideBar);
                        truncateOptions();
                    } catch(err) {}
                }
                try {
                    $$(".welcome-msg")[0].innerHTML = jsonObj.welcome;
                } catch(err) {}

            }             
        }   

    });

 
},

toggleLoader: function(mode) {		 
    if(mode == '1') {
        $('ajax_login_contents').hide();
        $('ajaxlogin_loader_holder').show();
    }

    else {
        $('ajax_login_contents').show();
        $('ajaxlogin_loader_holder').hide();
    }
},

checkAjaxStatus: function() {
    return ajaxloginForm.ajax;
},

setTopLinkHtml: function(class_name,data) {
 
    data = data.replace(/\/uenc\/.+?\//ig,'/');
 
    document.getElementsByClassName(class_name)[0].innerHTML = data;
       
    if(class_name == 'block-cart') {            
        document.getElementsByClassName(class_name)[0].className = '';
    }
 
}


}
 
var  ajaxloginForm = {

    init: function(id) {
        this.form =  new ajaxlogin(id);
    },
 
    getClass: function() {
        return this.form;
    },

    ajax: true,
    cartSidebar: true,
    voidLogin: true,
    hideForm: true,
    computePosition: true,
    horizontalCorrection: 0,
    verticalCorrection: 0

};

   
        
 