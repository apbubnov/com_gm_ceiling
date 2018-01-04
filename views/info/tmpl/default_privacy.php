<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Gm_ceiling
 * @author     Mikhail  <vms@itctl.ru>
 * @copyright  2016 Mikhail 
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
?>

<form >
	<h1> Согласие с обработкой персональных данных </h1>
	Общество с ограниченной ответственностью «Гильдия Мастеров» и Пользователь (дееспособное физическое лицо, достигшее 18 лет и пользующееся Сайтом www.test.gm-vrn.ru) выражает согласие с обработкой предоставляемых им персональных данных Оператору (ООО «Гильдия Мастеров») при соблюдении следующих условий:
	<ol class ="ol-levels">
		<li>Пользователь предоставляет свои персональные данные:
			<ol>
				<li>При оформлении покупки, создании Заказа Пользователь предоставляет Оператору следующие персональные данные:
					<ol>
						<li>Фамилия, имя, отчество;</li>
						<li>Адрес электронной почты;</li>
						<li>Номер контактного телефона;</li>
					</ol>
				</li>
				<li>При регистрации и авторизации на сайте Пользователь предоставляет Оператору следующие персональные данные:
					<ol>
						<li>Адрес электронной почты.</li>
					</ol>
				</li>
				<li>При получении регистрации на сайте Оператора и/или оформлении документов подтверждающих неисключительные права лицензиара на купленный у Оператора продукт (Свидетельство о приобретении) следующие персональные данные:
					<ol>
						<li>Фамилия, имя, отчество;</li>
						<li>Адрес электронной почты;</li>
						<li>Номер контактного телефона;</li>
						<li>Адрес;</li>
					</ol>
				</li>
			
		</li>
		<li>Оператор получает персональные данные Пользователя:
			<ol>
				<li>От пользователя при оформлении покупки, создании Заказа, регистрации/ авторизации на сайте Оператора.</li>
				<li> Предоставляя свои персональные данные при оформлении покупки, создании Заказа или регистрации/авторизации, Пользователь дает Оператору согласие на сбор, систематизацию, накопление, хранение, уточнение (обновление или изменение), использование, распространение, передачу третьим лицам, обезличивание, блокирование и уничтожение персональных данных.</li>
			</ol>
		</li>
		<li>Оператор использует персональные данные в целях
			<ol>
				<li>Регистрации Пользователей в системе Оператора;</li>
				<li>Оформления покупки Пользователем продуктов Оператора;</li>
				<li>Выдачи Пользователю купленного или бесплатного продукта Оператора;</li>
				<li>Информирования о нововведениях;</li>
			</ol>
		</li>
		<li>Согласие действительно в течение:
			<ol>
				<li>15 (пятнадцати) лет с момента оформления последнего Заказа или с момента регистрации на Сайте, в зависимости от того, какое событие наступило позднее,</li>
				<li>в течение срока действия неисключительных прав на использование продукта Оператора и/или сайтом Оператора, которые Пользователь приобрёл у Оператора
если пользователь не отозвал согласие на обработку его персональных данных в письменном виде (заявление на отзыв согласия). При получении такого заявления Оператор обязан будет прекратить обрабатывать персональные данные Пользователя, что повлечёт (или может повлечь) за собой полное закрытие аккаунта Пользователя на сайте Оператора и аннулирует регистрацию неисключительных прав Пользователя на продукт Оператора в системе Оператора. Оператор не несёт ответственности за финансовые потери, в результате подобного заявления Пользователя и в данном случае не компенсирует понесённые Пользователем затраты на приобретение неисключительных прав на продукт Оператора.</li>
			</ol>
		</li>
		<li> Оператор обязуется не разглашать полученные от Пользователя персональные данные. Не считается нарушением данного обязательства предоставление Оператором персональных данных партнёрам-дистрибьюторам, для исполнения обязательств перед Пользователями. Не считается нарушением обязательств разглашение персональных данных в соответствии с обоснованными и применимыми требованиями закона.</li>
		<li>Оператор вправе использовать технологию «cookies» и получать информацию об ip-адресе Пользователя. «Cookies» не содержат конфиденциальную информацию и не используются для установления личности Пользователя.</li>
		<li>В случае отзыва согласия на обработку персональных данных Пользователем, Оператор гарантирует, что вся полученная от Пользователя информация, в том числе логин и пароль, автоматически удаляется из баз данных Оператора, после чего Пользователь не будет иметь доступ к Сайту Оператора и к своей пользовательской панели на Сайте Оператора с их помощью. С целью отзыва согласия на обработку персональных данных Пользователю необходимо предоставить Оператору заявление на отзыв настоящего согласия с указанием адреса Сайта и названия Оператора, с подписью Пользователя и датой заявления.</li>
	</ol> 
	</ol>
</form>