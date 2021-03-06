<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Update controllers 
 *
 * @package    OC
 * @category   Update
 * @author     Chema <chema@garridodiaz.com>, Slobodan <slobodan.josifovic@gmail.com>
 * @copyright  (c) 2009-2013 Open Classifieds Team
 * @license    GPL v3
 */
class Controller_Panel_Update extends Auth_Controller {    


    public function action_index()
    {
        
        //force update check reload
        if (Core::get('reload')==1 )
            Core::get_updates(TRUE);
        
        $versions = core::config('versions');

        if (Core::get('json')==1)
        {
            $this->auto_render = FALSE;
            $this->template = View::factory('js');
            $this->template->content = json_encode($versions);  
        }
        else
        {
            Breadcrumbs::add(Breadcrumb::factory()->set_title(__('Updates')));
            $this->template->title = __('Updates');
        
            //check if we have latest version of OC
            if (key($versions)!=core::version)
                Alert::set(Alert::ALERT,__('You are not using latest version of OC, please update.').
                    '<br/><br/><a class="btn btn-primary update_btn" href="'.Route::url('oc-panel',array('controller'=>'update','action'=>'latest')).'">
                '.__('Update').'</a>');
            

            //pass to view from local versions.php         
            $this->template->content = View::factory('oc-panel/pages/tools/versions',array('versions'       =>$versions,
                                                                                           'latest_version' =>key($versions)));
        }        

    }

    /**
     * This function will upgrate configs that didn't existed in verisons below 2.0.3 
     */
    public function action_11()
    {
        // build array with new (missing) configs
        $configs = array(array('config_key'     =>'payment',
                               'group_name'     =>'thanks_page', 
                               'config_value'   =>''), 
                         array('config_key'     =>'general',
                               'group_name'     =>'blog', 
                               'config_value'   =>'0'), 
                         array('config_key'     =>'general',
                               'group_name'     =>'blog_disqus', 
                               'config_value'   =>''));
        

        
        // returns TRUE if some config is saved 
        $return_conf = Model_Config::config_array($configs);
        
        //clean cache
        Cache::instance()->delete_all();
        Theme::delete_minified();
            
        Alert::set(Alert::SUCCESS, __('Updated'));
        $this->request->redirect(Route::url('oc-panel', array('controller'=>'update', 'action'=>'index'))); 
    }

    /**
     * This function will upgrate DB that didn't existed in verisons below 2.0.6
     * changes added: config for custom field
     */
    public function action_latest()
    {
        
        $versions = core::config('versions'); //loads OC software version array 
        $download_link = $versions[key($versions)]['download']; //get latest download link
        $version = key($versions); //get latest version

    //@todo do a walidation of downloaded file and if its downloaded, trow error if something is worong
    // review all to be automatic

        $update_src_dir = DOCROOT."update"; // update dir 
        $fname = $update_src_dir."/".$version.".zip"; //full file name
        $folder_prefix = 'open-eshop-';
        $dest_dir = DOCROOT; //destination directory
        
        //check if exists file name
        if (file_exists($fname))  
            unlink($fname); 

        //create dir if doesnt exists
        if (!is_dir($update_src_dir))  
            mkdir($update_src_dir, 0775); 
          
        //download file
        $download = file_put_contents($fname, fopen($download_link, 'r'));

        //unpack zip
        $zip = new ZipArchive;
        // open zip file, and extract to dir
        if ($zip_open = $zip->open($fname)) 
        {
            $zip->extractTo($update_src_dir);
            $zip->close();  
        }   
        else 
        {
            Alert::set(Alert::ALERT, $fname.' '.__('Zip file faild to extract, please try again.'));
            $this->request->redirect(Route::url('oc-panel',array('controller'=>'update', 'action'=>'index')));
        }

        //files to be replaced / move specific files
        $copy_list = array('oc/config/routes.php',
                          'oc/classes/',
                          'oc/modules/',
                          'oc/vendor/',
                          'oc/bootstrap.php',
                          'themes/',
                          'languages/',
                          'index.php',
                          'README.md',);
      
        foreach ($copy_list as $dest_path) 
        { 
            $source = $update_src_dir.'/'.$folder_prefix.$version.'/'.$dest_path;
            $dest = $dest_dir.$dest_path;
            
            File::copy($source, $dest, TRUE);
        }
          
        //delete file when all finished
        File::delete($update_src_dir);
        $this->request->redirect(Route::url('oc-panel', array('controller'=>'update', 'action'=>str_replace('.', '', $version))));

    }

    
}