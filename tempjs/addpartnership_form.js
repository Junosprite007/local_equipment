/**
 * JavaScript for deleting partnerships in the add partnerships form.
 *
 * @module      local_equipment/addpartnership_form
 * @copyright   2024 onward Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby - CTO @ Fun Learning Company - funlearningcompany.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
define("local_equipment/addpartnership_form",["jquery","core/log","core/str"],(($,log,Str)=>({init:()=>{log.debug("Add Partnership Form JS initialized");const updateTrashIcons=()=>{$("fieldset").length>1?$(".remove-partnership").show():$(".remove-partnership").hide()};updateTrashIcons(),$(document).on("click",".remove-partnership",(function(){$(this).closest("fieldset").remove(),$(".partnership-header").each(((index,element)=>{Str.get_string("partnership","local_equipment",index+1).then((string=>{$(element).text(string)})).catch((error=>{log.error("Error updating partnership header:",error)}))})),(()=>{const partnershipsCount=$("fieldset").length;$('input[name="partnerships"]').val(partnershipsCount);const url=new URL(window.location.href);url.searchParams.set("repeatno",partnershipsCount),window.history.replaceState({},"",url)})(),$("fieldset").each(((index,fieldset)=>{$(fieldset).find("input, select, textarea").each(((_,element)=>{const name=$(element).attr("name");if(name){const newName=name.replace(/\[\d+\]/,"[".concat(index,"]"));$(element).attr("name",newName)}const id=$(element).attr("id");if(id){const newId=id.replace(/_\d+_/,"_".concat(index,"_"));$(element).attr("id",newId)}}))})),updateTrashIcons()}))}})));
