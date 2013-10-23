<?php
/**
 * @author: Mike Henry
 *
 * steps 8 and 12
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';


$files = array(
    '/display/ADDON/AdvSearch.AdvSearch.html',
    '/display/ADDON/AdvSearch.AdvSearchForm.html',
    '/display/ADDON/AdvSearch.AdvSearchHelp.html',
    '/display/ADDON/AdvSearch.html',
    '/display/ADDON/Advsearch.AdvSearchForm.tpl.html',
    '/display/ADDON/BannerX.html',
    '/display/ADDON/CronManager.html',
    '/display/ADDON/Databackup.html',
    '/display/ADDON/Discuss.Creating+a+Discuss+Theme.html',
    '/display/ADDON/FormIt.Tutorials+and+Examples.html',
    '/display/ADDON/FormIt.html',
    '/display/ADDON/FoundationX.html',
    '/display/ADDON/HybridAuth.Integrating+Twitter.html',
    '/display/ADDON/HybridAuth.html',
    '/display/ADDON/LexRating.html',
    '/display/ADDON/Login.ChangePassword.html',
    '/display/ADDON/Login.ConfirmRegister.html',
    '/display/ADDON/Login.Login.html',
    '/display/ADDON/Login.Profile.html',
    '/display/ADDON/Login.Register.html',
    '/display/ADDON/Login.Using+Pre+and+Post+Hooks.html',
    '/display/ADDON/MIGX.Create+selectable+and+sortable+Attributes+List+for+whatever+you+need+it.html',
    '/display/ADDON/MIGX.Fancybox-images+with+seperate+placeholders+in+Richtext-Content.html',
    '/display/ADDON/MIGX.Frontend-Usage.html',
    '/display/ADDON/MIGX.Simple+opening+hours+table.html',
    '/display/ADDON/MIGX.Varying+layout-boxes.Configurator-Version.html',
    '/display/ADDON/MIGX.Varying+layout-boxes.html',
    '/display/ADDON/MIGX.html',
    '/display/ADDON/MIGX.sortable+resourcelist.html',
    '/display/ADDON/MIGXdb.Create+a+basic+gallery-management+from+scratch+with+MIGXdb.html',
    '/display/ADDON/MIGXdb.Create+doodles+manager+with+help+of+MIGXdb.html',
    '/display/ADDON/MIGXdb.Manage+Child-Resources+in+a+grid-TV+with+help+of+MIGXdb.html',
    '/display/ADDON/MIGXdb.Manage+Events-Resources+in+a+CMP+with+help+of+MIGXdb.html',
    '/display/ADDON/MIGXdb.Tutorials.html',
    '/display/ADDON/MIGXdb.html',
    '/display/ADDON/Peoples.Peoples.html',
    '/display/ADDON/SimpleSearch.html',
    '/display/ADDON/Upload+to+Users+CMP.html',
    '/display/ADDON/Wayfinder.html',
    '/display/ADDON/fastField.html',
    '/display/ADDON/getResources.html',
    '/display/ADDON/getVimeo.html',
    '/display/ADDON/tagLister.tagLister.html',
    '/display/ADDON/virtuNewsletter.html',
    '/display/Evo1/Template+Basics.html',
    '/display/revolution20/Adding+a+Custom+TV+Type+-+MODX+2.2.html',
    '/display/revolution20/Commonly+Used+Template+Tags.html',
    '/display/revolution20/Contexts.html',
    '/display/revolution20/Creating+a+Resource+Class.html',
    '/display/revolution20/Custom+Manager+Pages+Tutorial.html',
    '/display/revolution20/Custom+Manager+Pages.html',
    '/display/revolution20/Custom+Output+Filter+Examples.html',
    '/display/revolution20/Custom+Resource+Classes.html',
    '/display/revolution20/Developing+an+Extra+in+MODX+Revolution%2C+Part+II.html',
    '/display/revolution20/Developing+an+Extra+in+MODX+Revolution.html',
    '/display/revolution20/Extending+modUser.html',
    '/display/revolution20/How+to+Write+a+Good+Chunk.html',
    '/display/revolution20/How+to+Write+a+Good+Snippet.html',
    '/display/revolution20/Loading+MODx+Externally.html',
    '/display/revolution20/MODX+and+Suhosin.html',
    '/display/revolution20/OnHandleRequest.html',
    '/display/revolution20/OnLoadWebDocument.html',
    '/display/revolution20/OnLoadWebPageCache.html',
    '/display/revolution20/PHP+Coding+in+MODx+Revolution%2C+Pt.+I.html',
    '/display/revolution20/Permissions.html',
    '/display/revolution20/PolicyTemplates.html',
    '/display/revolution20/Resource+Groups.html',
    '/display/revolution20/Snippets.html',
    '/display/revolution20/Symlink.html',
    '/display/revolution20/System+Settings.html',
    '/display/revolution20/Templating+Your+Snippets.html',
    '/display/revolution20/User+Groups.html',
    '/display/revolution20/Users.html',
    '/display/revolution20/emailsender.html',
    '/display/revolution20/modX.makeUrl.html',
    '/display/revolution20/modX.runSnippet.html',
    '/display/xPDO20/As+Object+and+Relational+Mapper.html',
    '/display/xPDO20/Defining+Relationships.html',
    '/display/xPDO20/remove.html',
    '/display/xPDO20/toArray.html',
    '/display/xPDO20/xPDO.getCollection.html',
    '/display/xPDO20/xPDO.log.html',
    '/display/xPDO20/xPDO.newObject.html',
    '/display/xPDO20/xPDO.query.html',
    '/display/xPDO20/xPDO.setOption.html',
    '/display/xPDO20/xPDOQuery.where.html',
);

$config = array(
    'source_dir' => 'C:/temp/modx/rtfm',
    'dest_dir' => 'C:/temp/rtfm-changes'
);

foreach ($files as $filename) {
    $source = $config['source_dir'] . $filename;
    $dest = $config['dest_dir'] . $filename;
    if (!file_exists(dirname($dest)))
        mkdir(dirname($dest), 0777, true);
    copy($source, $dest);
}
