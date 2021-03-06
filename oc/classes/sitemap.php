<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Sitemap 
 *
 * @package    OC
 * @category   Tools
 * @author     Chema <chema@garridodiaz.com>
 * @copyright  (c) 2009-2013 Open Classifieds Team
 * @license    GPL v3
 */

class Sitemap {

	
	/**
	 * 
	 * generate sitemap
	 */
	public static function generate($force = FALSE)
	{
	    //start time
        $start_time = microtime(TRUE);


        /**
         * only generate the sitemap if older than XXX
         */
        if ( time()>= Core::cache('sitemap_next') OR $force == TRUE)
        {

            $site_url = Core::config('general.base_url');

            // include class
            require Kohana::find_file('vendor/sitemap', 'SitemapGenerator');

            // create object
            $sitemap = new SitemapGenerator($site_url, DOCROOT);

            // will create also compressed (gzipped) sitemap
            $sitemap->createGZipFile = TRUE;

            // determine how many urls should be put into one file
            $sitemap->maxURLsPerSitemap = 10000;

            // sitemap file name
            $sitemap->sitemapFileName = 'sitemap.xml';

            // sitemap index file name
            $sitemap->sitemapIndexFileName = 'sitemap-index.xml';

            // robots file name
            //$sitemap->robotsFileName = 'robots.txt';
            

            //pages CMS 
            $pages =  new Model_Content();
            $pages = $pages->select('seotitle')->where('type','=','page')->where('status','=','1')->find_all();

            foreach($pages as $page)
            {
                $url = Route::url('page',  array('seotitle'=>$page->seotitle));
                $sitemap->addUrl($url, date('c',Date::mysql2unix($page->created)),  'monthly',    '0.5');
            }

          
            //categories
            $cats =  new Model_Category();
            $cats = $cats->select('seoname')->where('id_category','!=',1)->find_all();
            foreach($cats as $cat)
            {
                $url = Route::url('list',  array('category'=>$cat->seoname));
                $sitemap->addUrl($url, date('c'),  'daily',    '0.7');
            }
            
            //last products, you can modify this value at: general.feed_elements
            $products = DB::select('p.seotitle')
                ->select(array('c.seoname','category'),'p.title','p.created')
                ->from(array('products', 'p'))
                ->join(array('categories', 'c'),'INNER')
                ->on('p.id_category','=','c.id_category')
                ->where('p.status','=',Model_Product::STATUS_ACTIVE)
                ->order_by('created','desc')
                ->as_object()
                ->execute();

            foreach($products as $p)
            {
                $url= Route::url('product',  array('category'=>$p->category,'seotitle'=>$p->seotitle));
                $sitemap->addUrl($url, date('c'),  'monthly',    '0.5');
            }

            //all the blog posts
            $sitemap->addUrl(Route::url('blog'), date('c'), 'daily',    '0.7');
            $posts = new Model_Post();
            $posts = $posts->where('status','=', 1)
                    ->where('id_forum','IS',NULL)
                    ->order_by('created','desc')
                    ->find_all();
            foreach ($posts as $post) 
            {
                $url= Route::url('blog',  array('seotitle'=>$post->seotitle));
                $sitemap->addUrl($url, date('c'),  'monthly',    '0.5');
            }
            
            try
            {
                // create sitemap
                $sitemap->createSitemap();
                // write sitemap as file
                $sitemap->writeSitemap();
                // update robots.txt file
                //$sitemap->updateRobots();
                // submit sitemaps to search engines
                $result = $sitemap->submitSitemap();
                // shows each search engine submitting status
                // echo '<pre>'.print_r($result,1).'</pre>';
            }
            catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }

            $ret = 'Memory peak '.round(memory_get_peak_usage()/(1024*1024),2).' MB -';

        }//end if new jobs cache
        else//not any new job no need of generating the sitemap
        {
            $ret = __('No sitemap generated');
        }

        //setting the new cache to know when would be next generated
        Core::cache('sitemap_last',time());
        Core::cache('sitemap_next',time() + (24*60*60)); //24 hours

        return $ret.' Time: '.round( microtime(TRUE) - $start_time,2 ).'s';
    }//end sitemap generation

}