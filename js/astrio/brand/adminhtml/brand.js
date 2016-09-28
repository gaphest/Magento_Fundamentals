var Brand = {};

Brand.AttributesBridge = {
    tabsObject :false,
    bindTabs2Attributes : {},
    bind : function(tabId, attributesObject) {
        this.bindTabs2Attributes[tabId] = attributesObject;
    },
    getAttributes : function(tabId) {
        return this.bindTabs2Attributes[tabId];
    },
    setTabsObject : function(tabs) {
        this.tabsObject = tabs;
    },
    getTabsObject : function() {
        return this.tabsObject;
    },
    addAttributeRow : function(data) {
        $H(data).each( function(item) {
            if (this.getTabsObject().activeTab.name != item.key) {
                this.getTabsObject().showTabContent($(item.key));
            }
            this.getAttributes(item.key).addRow(item.value);
        }.bind(this));
    }
};

Brand.Attributes = Class.create();
Brand.Attributes.prototype = {
    config : {},
    containerId :null,
    initialize : function(containerId) {
        this.containerId = containerId;
    },
    setConfig : function(config) {
        this.config = config;
        Product.AttributesBridge.bind(this.getConfig().tab_id, this);
    },
    getConfig : function() {
        return this.config;
    },
    create : function() {
        var win = window.open(this.getConfig().url, 'new_attribute',
                'width=900,height=600,resizable=1,scrollbars=1');
        win.focus();
    },
    addRow : function(html) {
        var attributesContainer = $$('#group_fields' + this.getConfig().group_id + ' .form-list tbody')[0];
        Element.insert(attributesContainer, {
            bottom :html
        });

        var childs = attributesContainer.childElements();
        var element = childs[childs.size() - 1].select('input', 'select',
                'textarea')[0];
        if (element) {
            window.scrollTo(0, Position.cumulativeOffset(element)[1]
                    + element.offsetHeight);
        }
    }
};