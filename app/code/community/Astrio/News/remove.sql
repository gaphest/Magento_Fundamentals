DROP TABLE IF EXISTS `astrio_news_category_entity_datetime`;
DROP TABLE IF EXISTS `astrio_news_category_entity_decimal`;
DROP TABLE IF EXISTS `astrio_news_category_entity_int`;
DROP TABLE IF EXISTS `astrio_news_category_entity_text`;
DROP TABLE IF EXISTS `astrio_news_category_entity_url_key`;
DROP TABLE IF EXISTS `astrio_news_category_entity_varchar`;

DROP TABLE IF EXISTS `astrio_news_eav_attribute`;
DROP TABLE IF EXISTS `astrio_news_entity_datetime`;
DROP TABLE IF EXISTS `astrio_news_entity_decimal`;
DROP TABLE IF EXISTS `astrio_news_entity_int`;
DROP TABLE IF EXISTS `astrio_news_entity_text`;
DROP TABLE IF EXISTS `astrio_news_entity_url_key`;
DROP TABLE IF EXISTS `astrio_news_entity_varchar`;
DROP TABLE IF EXISTS `astrio_news_entity_store`;
DROP TABLE IF EXISTS `astrio_news_entity_category`;

DROP TABLE IF EXISTS `astrio_news_category_entity`;
DROP TABLE IF EXISTS `astrio_news_entity`;

DELETE FROM `eav_entity_type` WHERE `entity_type_code` = 'astrio_news_category';
DELETE FROM `eav_entity_type` WHERE `entity_type_code` = 'astrio_news';
DELETE FROM `core_resource` WHERE `code` = 'astrio_news_setup';