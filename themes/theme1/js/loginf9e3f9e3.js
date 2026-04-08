//Invalid Field 1.0.0 (c) JP Larson, Fiserv 2018.  All rights reserved.
//Code dependencies: none;
//Determines the validity of an input and applies the invalid class to the correct parent based on WCAG structure.
(function () {
	jQuery.fn.invalidField = function (options) {
		var settings = jQuery.extend({
			appliedClass: "invalid",
			listeners: "change keydown blur",
			position: "30%"
		}, options);
		try {
			var currentElement = jQuery(this),
			invalidIteration = function (valid, e) {
				var classed = function () {
					if (/radio/i.test(currentElement.get(0).type)) {
						return currentElement.parent().parent();
					} else {
						return currentElement.parent();
					}
				},
				eventType = e ? e.type : false;
				if (valid || eventType == "blur") {
					classed().removeClass(settings.appliedClass);
				} else {
					classed().addClass(settings.appliedClass);
				}
				return true;
			},
			scrollPosition = currentElement.offset().top - (jQuery(window).height() * (parseInt(settings.position) / 100)),
			listenerObject = function () {
				if (jQuery('[name=' + currentElement.get(0).name + ']').length > 1) {
					return jQuery('[name=' + currentElement.get(0).name + ']');
				} else {
					return currentElement;
				}
			};
            //Give user feedback on unfilled required input
            invalidIteration(currentElement.get(0).checkValidity());
            currentElement.focus();
            jQuery('html, body').scrollTop(scrollPosition);
            listenerObject().on(settings.listeners, function (e) {
            	invalidIteration(currentElement.get(0).checkValidity(), e);
            });

        } catch (err) {
        	console.log(err);
        }
        return this;
    }
}(jQuery));

//Field History 1.0.0 (c) JP Larson, Fiserv 2018.  All rights reserved.
//Code dependencies: none;
//Saves and applies the last value submitted from a form field.
(function () {
	jQuery.fn.fieldHistory = function (options) {
		var settings = jQuery.extend({
            obj: jQuery(this), //This is the input/field.
            form: jQuery(this).closest('form'), //This is the form used to detect submission, which triggers saving to the local storage.
            storageItem: "saved", //This is the name of the local storage entry.
            saveTrigger: "submit" //The event in which triggers the value being saved.
        }, options);
		if (settings.obj.length > 0) {
			settings.obj.each(function () {
				var thisObj = jQuery(this),
				storageItem = thisObj.get(0).name ? settings.storageItem + "-" + thisObj.get(0).name : settings.storageItem + "-" + thisObj.get(0).id,
				preference = localStorage.getItem(storageItem),
				type = thisObj.get(0).type,
				checkThis = function () {
					if (thisObj.val() == preference) {
						thisObj.trigger('change').trigger('click');
						thisObj.get(0).checked = true;
						return true;
					} else {
						thisObj.get(0).checked = false;
						return false;
					}
				},
				savePreference = function () {
					localStorage.setItem(storageItem, thisObj.val());
				},
				deletePreference = function () {
					localStorage.removeItem(storageItem);
				},
				setInput = function () {
					switch (type) {
						case "radio":
						checkThis();
						break;
						case "checkbox":
						checkThis();
						break;
						default:
						thisObj.val(preference);
						thisObj.trigger('change').trigger('click');
					}
				},
				saveIterate = function () {
					switch (type) {
						case "radio":
						if (thisObj.is(':checked')) {
							savePreference();
						}
						break;
						case "checkbox":
						if (thisObj.is(':checked')) {
							savePreference();
						} else {
							deletePreference();
						}
						break;
						default:
						if (thisObj.get(0).validity.valid) {
							savePreference();
						} else {
							deletePreference();
						}
					}
					return true;
				};
				try {
					if (preference) {
						setInput();
					}
					switch (settings.saveTrigger) {
						case "submit":
						settings.form.on('submit', function () {
							return saveIterate();
						});
						break;
						default:
						thisObj.on(settings.saveTrigger, function () {
							return saveIterate();
						});
					}
				} catch (err) {
					console.log(err);
				}
			});
		}
		return this;
	}
}(jQuery));

//Pseudo Select 1.0.6 (c) JP Larson, Fiserv 2018.  All rights reserved.
//1.0.6 fixed touch/mobile link bug.
//Creates a stylable select box
(function () {
	jQuery.fn.pseudoSelect = function (options) {
		var settings = jQuery.extend({
			select: jQuery(this),
            nodeName: "span", // Block level nodes cannot be used when the parent is a label.
            class: { //Class object. Added 1.0.3
                select: "ps-select", // This is the class used to identify the pseudo select.
                active: "active", // This is the active class used for the currently selected option.
                disable: "disabled", // This is the class used when the option is disabled.
                openUp: "open-up", // This is the class used when there is not enough room below the select to open down.
                ready: "ready" // This class is applied once the pseudo select has been appended and all settings complete. The animations are attached to this. Added: 1.0.3
            },
            parent: jQuery(this).parent('label') // This is the parent of the select, and/or the object in which the pseudo select will be placed.
        }, options),
            node = jQuery('<' + settings.nodeName + '></' + settings.nodeName + '>'), // Creates the object use to generate the options
            createPsOptions = function (psSelect, options) { // Creates the option objects
            	for (i = 0; i < options.length; i++) {
            		var newOption = node.clone().attr({
            			'data-option': options.eq(i).val(),
            			'data-html': options.eq(i).html()
            		}).html(options.eq(i).html());
            		if (i == 0) {
            			newOption.addClass(settings.class.active);
                        psSelect.attr('data-html', newOption.attr('data-html')); //Added 1.0.4
                    } else if (options.eq(i).attr('selected')) {
                    	psSelect.children().removeClass(settings.class.active);
                    	newOption.addClass(settings.class.active);
                        psSelect.attr('data-html', newOption.attr('data-html')); //Added 1.0.4
                    }
                    if (options.eq(i).attr('disabled')) {
                    	newOption.addClass(settings.class.disable);
                    }
                    if (options.eq(i).data('link')) {
                    	var target = options.eq(i).data('target') ? 'target = "' + options.eq(i).data('target') + '"' : "",
                    	optionLink = jQuery('<a href="' + options.eq(i).data('link') + '" tabindex="-1"' + target + '>' + options.eq(i).html() + '</a>');
                    	newOption.html(optionLink);
                    }
                    psSelect.append(newOption);
                }
                return psSelect.find(settings.nodeName);
            },
            changeSelect = function (select, option, initChange) { // Changes the selected option and triggers the change event on the select. Changed: 1.0.5
            	if (!isDisabled(option) && option.length >= 1) {
            		if (!changeExlude(option)) {
            			select.val(option.data('option'));
            			if (initChange) {
            				select.change();
            			}
            		} else {
            			select.val('');
            			if (initChange) {
            				select.change();
            			}
            		}
            		changeActive(option);
                    option.parent().attr('data-html', option.attr('data-html')); //Added 1.0.4
                }
            },
            changeActive = function (option) { // Added: 1.0.5
            	option.siblings().removeClass(settings.class.active);
            	option.addClass(settings.class.active);
            },
            changeExlude = function (option) {
            	if (option.children('a').length >= 1) {
            		return true;
            	} else {
            		return false;
            	}
            },
            isDisabled = function (option) {
            	if (option.hasClass(settings.class.disable)) {
            		return true;
            	} else {
            		return false;
            	}
            },
            allFocusables = function () {  //Changed: 1.0.5
            	return jQuery('select:not(:disabled), input:not([type=hidden]):not(:disabled), textarea:not(:disabled), button:not(:disabled), *:not([data-option]) > a');
            }, // Finds the objects that can be focused.
            focusNext = function (tabIndex) { // Determines the next object that can be focused. Changed: 1.0.5

            	if (!allFocusables().eq(tabIndex).get(0).disabled) {
            		allFocusables().eq(tabIndex).focus();
            	} else {
            		focusNext(tabIndex + 1);
            	}
            	return true;
            },
            followLink = function (active, follow) { // Follows any link activated within the options. Changed: 1.0.5
            	if (active.children('a').length > 0 && follow) {
            		var target = active.children('a').attr('target') ? active.children('a').attr('target') : "_self";
            		window.open(active.children('a').attr('href'), target);
            		return true;
            	} else if (active.children('a').length > 0) {
            		return true;
            	}
            	return false;
            },
            openDirection = function (psSelect, psOptions) { // Determines the direction the pseudo select can open, based on it's position within the window.
            if (psSelect.offset().top > window.innerHeight / 2 + window.pageYOffset) {
            	psSelect.addClass(settings.class.openUp);
            } else {
            	psSelect.removeClass(settings.class.openUp);
            }
        },
            psOptionMargin = function (psOptions) { // Sets the negative margin to close the pseudo select.
            	for (m = 1; m < psOptions.length; m++) {
            		psOptions.eq(m).css('margin-top', psOptions.eq(m)[0].getBoundingClientRect().height * -1 + "px");
            	}
            },
            moveToNext = function (currentSelect, active) { // Changed: 1.0.5
            	if (!isDisabled(active.next())) {
            		changeActive(active.next());
            	} else {
            		changeActive(active.next().next());
            	}
            },
            moveToPrev = function (currentSelect, active) { // Changed: 1.0.5
            	if (!isDisabled(active.prev())) {
            		changeActive(active.prev());
            	} else {
            		changeActive(active.prev().prev());
            	}
            };
            settings.select.each(function () {
            	try {
            		var currentSelect = jQuery(this),
            		selectLabel = settings.parent.length > 0 ? settings.parent.addClass(settings.class.select) : false,
                    selectIdentifier = currentSelect.attr('id') ? currentSelect.attr('id') : currentSelect.attr('name'),  //Added: 1.0.5
                    psSelect = node.clone().attr('data-select', selectIdentifier).attr('tabindex', 0),  //Changed: 1.0.5
                    options = currentSelect.find('option'),
                    psOptions = createPsOptions(psSelect, options),
                    tabIndex;

                // Initiate the pseudo select if the label is found.
                if (selectLabel) {

                    // Assignes the correct tab index to the select based on it's index within the allFocusables.
                    for (n = 0; n < allFocusables().length; n++) {
                    	if (allFocusables().eq(n).get(0) == currentSelect.get(0)) {
                    		tabIndex = n;
                    	}
                    }

                    // Add event handlers to the select and disable it's tab index.
                    selectLabel.on("click", function (e) {
                    	if (e.target.nodeName != "A") {
                    		e.preventDefault();
                    	}
                    });

                    // Add event handlers to the select and disable it's tab index.
                    currentSelect.on("keyup", function (e) {
                    	psSelect.focus();
                    }).on('change', function (e) { //Changed 1.0.6
                    	var option = currentSelect.find('[value="' + jQuery(this).val() + '"]'),
                    	psOption = psSelect.find('[data-option="' + jQuery(this).val() + '"]');
                    	if (option.data('link')) {
                    		followLink(psOption, true)
                    	}
                    	changeSelect(currentSelect, psOption, false);
                    }).attr('tabindex', -1);

                    // Add even handler to the pseudo select options.
                    psOptions.on("click", function (e) {
                    	changeSelect(currentSelect, jQuery(this), true);
                    	psSelect.blur();
                    });

                    // Add even handler to determine open direction.
                    jQuery(window).scroll(function () {
                    	openDirection(psSelect);
                    });
                    // Add even handlers to the pseudo select and assign keyboard functionality.
                    psSelect.on("focus", function () {
                    	openDirection(psSelect);
                    	currentSelect.addClass('hide');
                    }).on("blur", function () {
                    	currentSelect.removeClass('hide');
                    }).on("keydown", function (e) {
                    	var active = psSelect.find('.' + settings.class.active);
                    	switch (e.keyCode) {
                            case 38: // Up Arrow
                            e.preventDefault();
                            if (active.index() + 1 < psOptions.length && psSelect.hasClass(settings.class.openUp)) {
                            	moveToNext(currentSelect, active);
                            } else if (active.index() > 0) {
                            	moveToPrev(currentSelect, active);
                            }
                            break;
                            case 40: // Down Arrow
                            e.preventDefault();
                            if (active.index() > 0 && psSelect.hasClass(settings.class.openUp)) {
                            	moveToPrev(currentSelect, active);
                            } else if (active.index() + 1 < psOptions.length) {
                            	moveToNext(currentSelect, active);
                            }
                            break;
                            case 9: // Tab Key
                            e.preventDefault();
                                if (!followLink(active, false)) { // Changed: 1.0.5
                                	changeSelect(currentSelect, active, true);
                                }
                                focusNext(tabIndex + 1); // Changed: 1.0.5
                                break;
                            case 13: // Enter Key
                            e.preventDefault();
                                if (!followLink(active, true)) { // Changed: 1.0.5
                                	changeSelect(currentSelect, active, true);
                                }
                                focusNext(tabIndex + 1); // Changed: 1.0.5
                                break;
                                default:
                                focusNext(tabIndex + 1); // Changed: 1.0.5
                            }
                        });

                    // Append the pseudo select after the select.
                    currentSelect.after(psSelect);

                    // Collapse the pseudo select options.
                    psOptionMargin(psOptions);

                    // Recalculate the pseudo select margins on resize.
                    jQuery(window).on('resize transitionend', function () {
                    	psOptionMargin(psOptions);
                    });

                    // Adds the ready class which applies the transitions. Prevents the setup from animating.
                    setTimeout(function () {
                    	openDirection(psSelect);
                    	selectLabel.addClass(settings.class.ready);
                    }, 10);

                    psSelect.find('a').on('click', function (e) {
                    	e.stopPropagation();
                    });

                } else {
                	throw "The select " + currentSelect.get(0).id + " needs a parent label for pseudoSelect().";
                }
            } catch (err) {
            	console.log(err);
            }
        });
return this;
}
}(jQuery));

//Online Banking 1.0.0 (c) JP Larson, Fiserv 2018.  All rights reserved.
//Code dependencies: Invalid Field;
//Determines correct functionality based on the online banking versions.
(function () {
	jQuery.fn.onlineBanking = function (options) {
		var settings = jQuery.extend(true, {
            login: jQuery(this), //Login container object
            classObject: jQuery(this),// The object receiving the classes
            focusClass: "focus", // This class will be prepended to the input name and added to the class object when the input is focused
            select: jQuery('[name=loginTo]'), //Select object for switching Personal and Business
            server: "idemo", //OB Server Subdomain
            routingNumber: "199999996", //OB Routing Number
            profileNumber: "", //OB Profile Number (possibly BO 6.0 only)
            retail: { //Retail Object
                version: "5.1", //Use the Retail Online version number, or "custom" for the custom function. If that version isn't set, 5.1 will be used.
                class: "personal", //Class used to identify retail objects
                active: true, //Active (bool)
                custom: function () { //Custom Function (if presets don't work)
                return console.log(settings.retail.version + " has been selected for Retail Online. Additional code within the custom parameter may be needed.");
            }
        },
            business: { //Business Object
                version: "5.0", //Use the Business Online version number, or "custom" for the custom function. If that version isn't set, 5.0 will be used.
                class: "business", //Class used to identify business objects
                active: true, //Active (bool)
                custom: function () { //Custom Function (if presets don't work)
                return console.log(settings.retail.version + " has been selected for Business Online. Additional code within the custom parameter may be needed.");
            }
        },
        other: {
                class: "other", //Class used to identify objects
                active: false, //Active (bool)
                custom: function () { //Custom Function (there are no presets for other)
                	return console.log("Other has been activated for Online Banking. Additional code within the custom parameter may be needed.");
                }
            },
            success: function () { } //Success Function (callback)
        }, options),
            ob = { //Online banking functions. This replaces the doLoginRefresh function.
                retail: { //Retail preset functions. More functions can be added by specifying the version with the object key.
                    "default": function () { //Default function based on RO 5.1
                    	settings.login.find("form." + settings.retail.class).on('submit', function () {
                    		this.action = "https://" + settings.server + ".secureinternetbank.com/pbi_pbi1151/login/remote/" + settings.routingNumber;
                    	});
                    	return true;
                    },
                    "custom": function () { //Calls the custom function from settings
                    	return settings.retail.custom();
                    }
                },
                business: { //Business preset functions. More functions can be added by specifying the version with the object key.
                    "default": function () { //Default function based on BO 5.0
                    	settings.login.find("form." + settings.business.class).on('submit', function () {
                    		if (typeof this.nmUID !== "undefined" && typeof this.nmRTN !== "undefined") {
                    			this.nmUID.value = this.AccessID.value;
                    			this.nmRTN.value = settings.routingNumber;
                    			this.action = "https://" + settings.server + ".secureinternetbank.com/ebc_ebc1961/ebc1961.ashx?WCI=Process&WCE=RemoteLogon&IRL=T&MFA=2&RT=" + settings.routingNumber;
                    		} else {
                    			console.log("nmUID or nmRTN does not exist.");
                    			return false;
                    		}
                    	});
                    	return true;
                    },
                    "6.0": function () { //BO 6.0 function
                    	jQuery.ajax({
                    		url: "https://" + settings.server + ".secureinternetbank.com/EBC_EBC1151/js/RemoteLogon",
                    		dataType: "script",
                    		success: function () {
                    			var submitCallback = function () {
                    				console.log("BO 6.0 Submitted");
                    			},
                    			errorCallback = function () {
                    				console.log("BO 6.0 Errored. Make sure the client has added their stage and all domains to their BO profile.");
                    			},
                    			args = {
                                        errorCallback: errorCallback, //Optional call to custom JavaScript for error handling
                                        submitCallback: submitCallback, //Optional call to custom JavaScript for submission handling
                                        applicationPath: "https://" + settings.server + ".secureinternetbank.com/EBC_EBC1151",
                                        formId: "ebc-form",
                                        passwordId: "ebc-password",
                                        routingTransit: settings.routingNumber,
                                        profileNumber: settings.profileNumber,
                                        usernameId: "ebc-username"
                                    };

                                    new EBC.RemoteLogin(args);
                                }
                            });
                    	return true;
                    },
                    "custom": function () { //Calls the custom function from settings
                    	return settings.business.custom();
                    }
                }
            },
            OnSelectionChange = function () { //Online Banking Select Change
            	var selectValue = function (select) {
            		if (select.get(0).nodeName == "INPUT" && select.get(0).type == "radio") {
            			for (i = 0; i < select.length; i++) {
            				if (select.eq(i).get(0).checked) {
            					return select.eq(i).get(0).value;
            				}
            			}
            		} else if (select.get(0).nodeName == "SELECT") {
            			return select.val();
            		}
            	};
            	settings.classObject.removeClass(settings.retail.class).removeClass(settings.business.class).removeClass(settings.other.class).addClass(selectValue(settings.select));
            	settings.login.find('form input, form select, form textarea, form button').each(function () {
            		this.disabled = true;
            	});
            	settings.login.find('.' + selectValue(settings.select)).each(function () {
            		jQuery(this).find('input, select, textarea, button').each(function () {
            			this.disabled = false;
            		});
            	});
            	return true;
            },
            validate = function (form) { //Validates all inputs. Requires the invalid field plugin.
            	var requiredElements = form.find(':invalid:not([disabled]):not(form):not(fieldset)');
            	if (settings.select.get(0).validity.valid) {
            		OnSelectionChange(login, settings.select);
            	}
            	for (i = 0; i < requiredElements.length; i++) {
            		var currentElement = jQuery(requiredElements[i]);
            		currentElement.invalidField();
            		return false;
            	}
            	return true;
            },
            inputFocus = function (obj) { // Creates and applied a class to the class object when an input is focused.
            	var thisClass = settings.focusClass + "-" + obj.name;
            	settings.classObject.addClass(thisClass);
            	jQuery(obj).on('blur', function () {
            		settings.classObject.removeClass(thisClass);
            	});
            },
            init = function () { //Initializes the retial, business, and other functions.
            	for (var key in ob) {
            		if (settings.hasOwnProperty(key)) {
            			if (settings[key].active) {
            				if (typeof ob[key][settings[key].version] === "function") {
            					ob[key][settings[key].version]();
            				} else {
            					ob[key]["default"]();
            				}
            			}
            		}
            	}
            	if (typeof settings.other.custom === "function" && settings.other.active) {
            		settings.other.custom();
            	}
            	return true;
            };
            try {

            	settings.select.on('change', function () {
            		OnSelectionChange(login, settings.select);
            	})

            	settings.login.find('[type=submit]').on('click', function () {
            		return validate(settings.login);
            	});

            	settings.login.find('input, select, textarea').on('focus', function () {
            		return inputFocus(this);
            	});

            	init();

            	settings.success();

            } catch (err) {
            	console.log(err);
            }
            return this;
        }
    }(jQuery));
/* DOM Ready -----------------------------------------------*/
jQuery(document).ready(function () {

    //Initialize Online Banking
    // jQuery('#login').onlineBanking({
    //     retail: {
    //         version: "custom",
    //         custom: function () {
    //             jQuery("form.personal").on('submit', function () {
    //                 this.action = "https://primepremierbn.cbzsecure.com/auth";
    //             });
    //             return false;
    //         }
    //     },
    //     business: {
    //         version: "custom",
    //         custom: function () {
    //             jQuery("form.business").on('submit', function () {
    //                 this.action = "https://primepremierbnbiz.cbzsecure.com/auth";
    //             });
    //             return false;
    //         }
    //     }
    // });

    //Initialize Pseudo Select
    jQuery('#login select').pseudoSelect();

    //Initialize Field History
    jQuery('[name=loginTo]').fieldHistory({
    	form: jQuery('#login').find('form'),
    	saveTrigger: "change"
    });

});