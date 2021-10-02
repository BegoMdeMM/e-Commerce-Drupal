<?php

/* modules/geolocation/templates/geolocation-map-formatter.html.twig */
class __TwigTemplate_04c2dba448b7c20b89dfcc3db776b51c861d91ab70a010f5da29e1fbcff14c6c extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $tags = array("if" => 15, "for" => 19);
        $filters = array();
        $functions = array();

        try {
            $this->env->getExtension('sandbox')->checkSecurity(
                array('if', 'for'),
                array(),
                array()
            );
        } catch (Twig_Sandbox_SecurityError $e) {
            $e->setTemplateFile($this->getTemplateName());

            if ($e instanceof Twig_Sandbox_SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

        // line 15
        echo "<div id=\"";
        echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["uniqueid"]) ? $context["uniqueid"] : null), "html", null, true));
        echo "\" class=\"geolocation-formatter-map-wrapper\" ";
        if ( !twig_test_empty((isset($context["latitude"]) ? $context["latitude"] : null))) {
            echo " data-map-lat=\"";
            echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["latitude"]) ? $context["latitude"] : null), "html", null, true));
            echo "\" ";
        }
        echo " ";
        if ( !twig_test_empty((isset($context["longitude"]) ? $context["longitude"] : null))) {
            echo " data-map-lng=\"";
            echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, (isset($context["longitude"]) ? $context["longitude"] : null), "html", null, true));
            echo "\" ";
        }
        echo ">
  <div class=\"geolocation-google-map\"></div>
  ";
        // line 17
        if ( !twig_test_empty((isset($context["locations"]) ? $context["locations"] : null))) {
            // line 18
            echo "    <div class=\"geolocation-common-map-locations\">
        ";
            // line 19
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable((isset($context["locations"]) ? $context["locations"] : null));
            foreach ($context['_seq'] as $context["_key"] => $context["location"]) {
                // line 20
                echo "            ";
                echo $this->env->getExtension('sandbox')->ensureToStringAllowed($this->env->getExtension('drupal_core')->escapeFilter($this->env, $context["location"], "html", null, true));
                echo "
        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['location'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 22
            echo "    </div>
  ";
        }
        // line 24
        echo "</div>
";
    }

    public function getTemplateName()
    {
        return "modules/geolocation/templates/geolocation-map-formatter.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  83 => 24,  79 => 22,  70 => 20,  66 => 19,  63 => 18,  61 => 17,  43 => 15,);
    }
}
/* {#*/
/* /***/
/*  * @file*/
/*  * Default microdata theme implementation for a geolocation with latitude, longitude.*/
/*  **/
/*  * Available variables:*/
/*  * - lat: The latitude value.*/
/*  * - lng: The longitude value.*/
/*  **/
/*  * @see template_preprocess()*/
/*  **/
/*  * @ingroup themeable*/
/*  *//* */
/* #}*/
/* <div id="{{ uniqueid }}" class="geolocation-formatter-map-wrapper" {% if latitude is not empty %} data-map-lat="{{ latitude }}" {% endif %} {% if longitude is not empty %} data-map-lng="{{ longitude }}" {% endif %}>*/
/*   <div class="geolocation-google-map"></div>*/
/*   {% if locations is not empty %}*/
/*     <div class="geolocation-common-map-locations">*/
/*         {%  for location in locations %}*/
/*             {{ location }}*/
/*         {% endfor %}*/
/*     </div>*/
/*   {% endif %}*/
/* </div>*/
/* */
