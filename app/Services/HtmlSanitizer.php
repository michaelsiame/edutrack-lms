<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlSanitizer
{
    private static ?HTMLPurifier $purifier = null;

    private static function getPurifier(): HTMLPurifier
    {
        if (self::$purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
            $config->set('HTML.Allowed', 'p,div,br,hr,h1,h2,h3,h4,h5,h6,blockquote,pre,code,ul,ol,li,table,thead,tbody,tr,th,td,strong,em,span,a[href|title],img[src|alt|width|height|class|style],iframe[src|width|height|allowfullscreen|frameborder],s,strike,sub,sup,u,abbr,acronym,caption,col,colgroup,dl,dt,dd,figure,figcaption');
            $config->set('CSS.AllowedProperties', 'font-weight,font-style,text-decoration,color,background-color,text-align,width,height,max-width,border,margin,padding,float,display,font-size,line-height');
            $config->set('AutoFormat.RemoveEmpty', false);
            $config->set('AutoFormat.RemoveEmpty.RemoveNbsp', false);
            $config->set('HTML.SafeIframe', true);
            $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');
            $config->set('URI.AllowedSchemes', ['http', 'https', 'mailto', 'tel']);
            $config->set('Attr.AllowedFrameTargets', '_blank,_self,_top,_parent');
            $config->set('Attr.AllowedRel', 'nofollow,noopener,noreferrer');
            $config->set('Core.Encoding', 'UTF-8');
            $config->set('Cache.SerializerPath', storage_path('app/htmlpurifier'));

            // figure/figcaption are HTML5 and absent from the 4.01 doctype, so
            // they must be registered as custom elements or purification throws
            $config->set('HTML.DefinitionID', 'edutrack-lesson-html');
            $config->set('HTML.DefinitionRev', 2);
            if ($def = $config->maybeGetRawHTMLDefinition()) {
                $def->addElement('figure', 'Block', 'Flow', 'Common');
                $def->addElement('figcaption', 'Block', 'Flow', 'Common');
                $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
            }

            self::$purifier = new HTMLPurifier($config);
        }

        return self::$purifier;
    }

    public static function clean(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        return self::getPurifier()->purify($html);
    }
}
