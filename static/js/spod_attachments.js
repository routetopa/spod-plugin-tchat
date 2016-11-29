var SPODFileAttachment = function(params) {
    $.extend(this, JSON.parse(params));
    this.$context = $('#' + this.uid);
    //this.$previewCont = $('.ow_file_attachment_preview', this.$context);
    this.$previewCont = $('#ow_file_attachment_preview_' + this.uid);
    this.inputCont = this.selector ? $(this.selector) : $('a.attach', this.$context);

    var self = this;
    var items = {};
    var itemId = 1;

    var refreshClasses = function() {
        var itemIndex = 1;
        $.each(items,
            function(index, data) {
                data['html'].removeClass('ow_file_attachment_block1').removeClass('ow_file_attachment_block2').addClass('ow_file_attachment_block' + itemIndex);
                itemIndex = itemIndex == 1 ? 2 : 1;
            }
        );
    };

    this.addItem = function(data, loader, customDeleteUrl, customConfirmation) {
        data['html'] = $('<div><div class="ow_file_attachment_info">' +
            '<div class="ow_file_attachment_name">' + data.name + ' <span class="ow_file_attachment_size" style="display: inline-block;">(' + data.size + 'KB)</span></div>' +
            '<div class="ow_file_attachment_preload" style="display:' + (loader ? 'block' : 'none') + ';"></div>' +
            '<a href="javascript://" class="ow_file_attachment_close"></a>' +
            '</div></div>');

        if (typeof customDeleteUrl != "undefined")
        {
            data['customDeleteUrl'] = customDeleteUrl;
        }

        //self.$previewCont.append(data['html']);
        self.$previewCont.html(data['html']);
        self.$previewCont.show();
        //$("#file_attachment_preview")[0].html(data['html']);
        OW.trigger('base.attachment_rendered', {'data' : data}, this);

        $('.ow_file_attachment_close', data['html']).bind('click', function() {
            var confirmed = true;

            if (typeof customConfirmation != "undefined") {
                confirmed = confirm(customConfirmation);
            }

            if (confirmed) {
                self.deleteItem(data['id'], customDeleteUrl);
            }
        });
    };

    /**
     * Render uploaded items
     *
     * @param object uploadedItems
     * @param string customDeleteUrl
     * @param string customConfirmation
     */
    this.renderUploaded = function(uploadedItems, customDeleteUrl, customConfirmation) {
        $.each(uploadedItems, function(index, data) {
            self.addItem(data, true, customDeleteUrl, customConfirmation);
            itemId++;
        });

        $.extend(items, uploadedItems);
        refreshClasses();
    }

    this.initInput = function() {
        var $input = $('<input class="mlt_file_input" type="file"' + (this.multiple ? ' multiple=""' : '') + ' name="ow_file_attachment[]" />');
        this.inputCont.append($input);

        $input.change(
            function(e) {
                var inItems = {};

                // check if files array is available
                if (this.files != undefined) {
                    var elData;
                    for (var i = 0; i < this.files.length; i++) {
                        elData = this.files[i];
                        inItems[itemId] = {id: itemId++, name: elData.name, size: (Math.round(elData.size / 1024))};
                    }
                }
                else {

                }

                if (self.showPreview) {
                    $.each(inItems,
                        function(index, data) {
                            self.addItem(data, true);
                        }
                    );
                }

                OW.trigger('base.add_attachment_to_queue', {'pluginKey': self.pluginKey, 'uid': self.uid, 'items': items});

                $.extend(items, inItems);

                if (self.showPreview) {
                    refreshClasses();
                }

                self.submitFiles(inItems);
            }
        );
    };

    this.submitFiles = function(data) {
        var idList = [], idListIndex = 0;
        $.each(data, function() {
                idList[idListIndex++] = this.id;
            }
        );

        var index = idList.join('_');
        var nameObj = {};

        $.each(items, function(index, item) {
            nameObj[item.name] = item.id;
        });

        $form = $('<form method="post" action="' + self.submitUrl + '?flUid=' + self.uid + '" enctype="multipart/form-data" target="form_' + index + '"><input type="hidden" name="flData" value="' +
            encodeURIComponent(JSON.stringify(nameObj)) + '" /><input type="hidden" name="flUid" value="' + self.uid + '"><input type="hidden" name="pluginKey" value="' + self.pluginKey + '">' +
            '</form>')
            .append($('input[type=file]', self.inputCont));
//        $form.appendTo($('body'));
        $('<div style="display:none" id="hd_' + index + '"><div>').appendTo($('body'))
            .append($('<iframe name="form_' + index + '"></iframe>'))
            .append($form);
        $form.submit();

        self.initInput();
    };

    this.updateItems = function(data) {

        if (!data.result && data.noData) {
            OW.error(data.message);
            return;
        }
        var indexList = [];

        if (data.items) {
            $.each(data.items, function(index, item) {
                indexList.push(index);

                if (item.result) {
                    items[index]['dbId'] = item['dbId'];
                    if( items[index]['cancelled'] ){
                        self.deleteItem(index);
                        return;
                    }

                    if (self.showPreview) {
                        $('.ow_file_attachment_preload', items[index]['html']).hide();
                    }

                } else {
                    self.deleteItem(index);
                    if (self.showPreview) {
                        OW.error(item.message);
                    }
                }
            });

            OW.trigger('base.update_attachment', {'pluginKey': self.pluginKey, 'uid': self.uid, 'items': data.items, 'url' : data.url, 'type' : data.type});
        }

        $('#hd_' + indexList.join('_')).remove();
    };

    this.deleteItem = function(id, customDeleteUrl) {
        OW.trigger('base.attachment_deleted', {'id' : id}, this);

        if (self.showPreview) {
            items[id]['html'].remove();
        }

        if( !items[id]['dbId'] ){
            items[id]['cancelled'] = true;
            return;
        }

        $.ajax({
            url: (typeof customDeleteUrl == "undefined" ? self.deleteUrl : customDeleteUrl),
            data: {id: items[id]['dbId']},
            method: "POST"
        });

        delete items[id];
        if (self.showPreview) {
            refreshClasses();
        }
    };

    this.reset = function(id, callback) {
        self.uid = id;

        if (typeof callback != "undefined") {
            callback.call({}, items);
        }

        if (self.showPreview) {
            $.each(items,
                function(index, data) {
                    data['html'].remove();
                }
            );

            refreshClasses();
        }

        items = {};
        itemId = 1;

    };

    $.each(self.lItems, function(index, lItem) {
        items[itemId] = {id: itemId++, name: lItem.name, size: lItem.size, dbId: lItem.dbId};
    });

    if (self.showPreview) {
        $.each(items,
            function(index, data) {
                self.addItem(data, false);
            }
        );

        refreshClasses();
    }

    this.initInput();
};

OW.bind('base.file_attachment', function(data) {
    if (window.tchatAttachment[data.uid]) {
        window.tchatAttachment[data.newUid] = window.tchatAttachment[data.uid];
        delete window.tchatAttachment[data.uid];
        window.tchatAttachment[data.newUid].reset(data.newUid);
    }
});
