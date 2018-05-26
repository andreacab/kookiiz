<?php
	/**********************************************************
    Title: User form
    Authors: Kookiiz Team
    Purpose: HTML content of the user form
    ***********************************************************/

    //Dependencies
    require_once '../class/session.php';

    //Start session
    Session::start();

    //Init handlers
    $Lang = LangDB::getHandler(Session::getLang());
?>
<form method="post" action="/dom/signup.php" target="iframe_signup">
    <input type="hidden" name="network" value="" />

    <!-- Step 1 -->
    <div class="step" data-step="1" style="display:none">
        <h5 class="message"><?php $Lang->p('SUBSCRIBE_TEXT', 2); ?></h5>
        <p class="message">
            <span>* <?php $Lang->p('SUBSCRIBE_TEXT', 3); ?></span>
            <button type="button" class="social facebook_100 text_color0 bold left" data-network="facebook"><?php $Lang->p('ACTIONS', 17); ?></button>
            <?php if(false) { //Hide Twitter connect button ?>
            <button type="button" class="social twitter_100 text_color0 bold left" data-network="twitter"><?php $Lang->p('ACTIONS', 17); ?></button>
            <?php } ?>
        </p>
        <div class="section narrow">
            <ul class="center fields">
                <li>
                    <span class="bold"><?php $Lang->p('USER_PROPERTIES', 8); ?></span>
                </li>
                <li>
                    <img class="avatar click" src="<?php echo '/pictures/users/', C::USER_PIC_DEFAULT; ?>" alt="" />
                    <input type="hidden" name="pic_id" value="0" />
                </li>
                <li>
                    <button type="button" class="avatar upload button_80"><?php $Lang->p('ACTIONS', 14); ?></button>
                    <button type="button" class="avatar delete button_80" style="display:none"><?php $Lang->p('ACTIONS', 15); ?></button>
                </li>
            </ul>
        </div>
        <div class="section">
            <ul class="fields">
                <li>
                    <p class="caption bold"><?php $Lang->p('USER_PROPERTIES', 2); ?>*</p>
                    <p class="tiny bold error"></p>
                    <p>
                        <span class="input_wrap size_220">
                            <input type="text" name="email" />
                        </span>
                        <img class="icon15 accept" src="<?php C::p('ICON_URL'); ?>" style="display:none" alt="<?php $Lang->p('VARIOUS', 22); ?>" />
                        <img class="icon15 delete" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('VARIOUS', 21); ?>" />
                    </p>
                </li>
                <li>
                    <p class="caption bold"><?php $Lang->p('USER_PROPERTIES', 3); ?>*</p>
                    <p class="tiny bold error"></p>
                    <p>
                        <span class="input_wrap size_220">
                            <input type="password" name="password1" maxlength="<?php C::p('USER_PASSWORD_MAX'); ?>" />
                        </span>
                        <img class="icon15 accept" src="<?php C::p('ICON_URL'); ?>" style="display:none" alt="<?php $Lang->p('VARIOUS', 22); ?>" />
                        <img class="icon15 delete" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('VARIOUS', 21); ?>" />
                    </p>
                </li>
                <li>
                    <p class="caption bold"><?php $Lang->p('USER_PROPERTIES', 4); ?>*</p>
                    <p class="tiny bold error"></p>
                    <p>
                        <span class="input_wrap size_220">
                            <input type="password" name="password2" maxlength="<?php C::p('USER_PASSWORD_MAX'); ?>" />
                        </span>
                        <img class="icon15 accept" src="<?php C::p('ICON_URL'); ?>" style="display:none" alt="<?php $Lang->p('VARIOUS', 22); ?>" />
                        <img class="icon15 delete" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('VARIOUS', 21); ?>" />
                    </p>
                </li>
            </ul>
        </div>
        <div class="section">
            <ul class="fields">
                <li>
                    <p class="caption bold"><?php $Lang->p('USER_PROPERTIES', 0); ?>*</p>
                    <p class="tiny bold error"></p>
                    <p>
                        <span class="input_wrap size_220">
                            <input type="text" name="firstname" maxlength="<?php C::p('USER_FIRSTNAME_MAX'); ?>" />
                        </span>
                        <img class="icon15 accept" src="<?php C::p('ICON_URL'); ?>" style="display:none" alt="<?php $Lang->p('VARIOUS', 22); ?>" />
                        <img class="icon15 delete" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('VARIOUS', 21); ?>" />
                    </p>
                </li>
                <li>
                    <p class="caption bold"><?php $Lang->p('USER_PROPERTIES', 1); ?>*</p>
                    <p class="tiny bold error"></p>
                    <p>
                        <span class="input_wrap size_220">
                            <input type="text" name="lastname" maxlength="<?php C::p('USER_LASTNAME_MAX'); ?>" />
                        </span>
                        <img class="icon15 accept" src="<?php C::p('ICON_URL'); ?>" style="display:none" alt="<?php $Lang->p('VARIOUS', 22); ?>" />
                        <img class="icon15 delete" src="<?php C::p('ICON_URL'); ?>" alt="<?php $Lang->p('VARIOUS', 21); ?>" />
                    </p>
                </li>
                <li>
                    <p>
                        <span class="bold"><?php $Lang->p('USER_PROPERTIES', 5); ?></span>
                        <select name="lang">
                        <?php
                            $lang               = Session::getLang();
                            $languages          = C::get('LANGUAGES');
                            $languages_names    = C::get('LANGUAGES_NAMES');
                            asort($languages);
                            foreach($languages as $code)
                            {
                                $lang_id = array_search($code, $languages);
                                echo '<option value="', $code, '"', ($code == $lang ? ' selected="selected">' : '>'), $languages_names[$lang_id], '</option>';
                            }
                        ?>
                        </select>
                    </p>
                </li>
                <li>
                    <p>
                        <input type="checkbox" name="terms" />
                        <span>
                            <?php $Lang->p('SUBSCRIBE_TEXT', 0); ?>
                            <a class="terms" href="javascript:Utilities.terms_display();"><?php $Lang->p('SUBSCRIBE_TEXT', 1); ?></a>
                        </span>
                    </p>
                </li>
            </ul>
        </div>
    </div>

    <!-- Step 2 -->
    <div class="step" data-step="2" style="display:none"></div>

    <div class="buttons center">
        <button type="button" class="button_shiny next" style="display:none"><?php $Lang->p('ACTIONS', 40); ?></button>
        <button type="submit" class="button_shiny submit" style="display:none"><?php $Lang->p('ACTIONS', 2); ?></button>
        <span class="click back"><?php $Lang->p('WELCOME_TEXTS', 1); ?></span>
    </div>
</form>
<iframe name="iframe_signup" style="display:none"></iframe>