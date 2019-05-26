/**
 * scripts de la page sbm-mail/index/index.phtml 
 * 
 * @project sbm
 * @filesource mail/index.js
 * @encodage UTF-8
 * @author DAFAP Informatique - Alain Pomirol (dafap@free.fr)
 * @date 26 mai 2019
 * @version 2019-2.5.0
 */
var bodyWrapper = $("#body-wrapper");
var label = $('<label class="sbm-label required" for="mail-body">Message</label>'); 
var textArea = $('<textarea name="body" id="mail-body">'+contenu+'</textarea>');
bodyWrapper.append(label);
bodyWrapper.append(textArea);