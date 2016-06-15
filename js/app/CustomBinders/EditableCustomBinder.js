/*globals ko */
ko.bindingHandlers.editable = {
    init: function(elementDom, valueAccessor) {
        var element = $(elementDom);
        var bindingParameters = ko.unwrap(valueAccessor());

        element.click(function() {
            var inputElement = $('<input type="text" />');
            
            element.hide();
            inputElement.val(element.text());
            element.parent().append(inputElement);
            inputElement.focus();
            
            inputElement.keyup(function(event){
                if(event.keyCode == 13) {
                    var newName = inputElement.val();
                    inputElement.remove();
                    element.text(newName);
                    element.show();
                    
                    var functionParams = bindingParameters.args.slice(0);
                    functionParams.unshift(newName)
                    bindingParameters.update.apply(bindingParameters, functionParams);
                }
            });
        });
    }
};