<?php
/**
 * @author WP Cloud Plugins
 * @copyright Copyright (c) 2023, WP Cloud Plugins
 *
 * @since       2.0
 * @see https://www.wpcloudplugins.com
 */

namespace TheLion\OutoftheBox;

defined('ABSPATH') || exit;

// Exit if no permission
if (
    !Helpers::check_user_role(Settings::get('permissions_link_users'))
) {
    exit;
}

$installation = [
    'steps' => [
        1 => [
            'title' => esc_html__('Install the plugin', 'wpcloudplugins'),
            'description' => esc_html__('Navigate to Plugins → Add New, Click Upload Plugin and select the ZIP package. Click Install Now and then Activate to enable the plugin.'),
            'action_url' => admin_url('plugin-install.php?tab=upload'),
            'completed' => true,
        ],
        2 => [
            'title' => esc_html__('Activate License', 'wpcloudplugins'),
            'description' => esc_html__('Begin the license activation using your Envato Account or license code.'),
            'action_url' => admin_url('admin.php?page=OutoftheBox_settings'),
            'action_title' => esc_html__('Activate', 'wpcloudplugins'),
            'completed' => License::is_valid(),
        ],
        3 => [
            'title' => esc_html__('Connect Account(s)', 'wpcloudplugins'),
            'description' => esc_html__('On the main plugin options page click Add Account. Authorize the plugin by logging into your Cloud account and granting access.'),
            'action_url' => admin_url('admin.php?page=OutoftheBox_settings'),
            'action_title' => esc_html__('Connect', 'wpcloudplugins'),
            'completed' => Accounts::instance()->has_accounts(),
        ],
        4 => [
            'title' => esc_html__('Create your first Module', 'wpcloudplugins'),
            'description' => esc_html__('Create or edit a page/post, then add the WP Cloud Plugin block via your favorite editor. Configure the module and click Publish or Update.'),
            'action_url' => admin_url('admin.php?page=OutoftheBox_settings_shortcodebuilder'),
            'action_title' => esc_html__('Create module', 'wpcloudplugins'),
            'completed' => count(Modules::get_modules()) > 1,
        ],
    ],
];

// Check if all steps are completed
$all_steps_completed = true;
foreach ($installation['steps'] as $step) {
    if (!$step['completed']) {
        $all_steps_completed = false;

        break;
    }
}

?>
<div id="wpcp" class="wpcp-app wpcp-get-started hidden" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
    <div class="z-10 bg-gray-100 relative">
        <div class="min-h-[calc(100vh-32px)] flex flex-col">

            <div class="fixed inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
                <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#590E54] opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
            </div>

            <div class="flex grow flex-col ">
                <!-- Page -->
                <main class="flex flex-col flex-1">

                    <div class="flex-1 w-full mx-auto px-4 py-4 sm:px-6 lg:px-8">
                        <div class="flow-root min-w-full overflow-auto">

                            <?php if (!$all_steps_completed) {?>

                            <!-- Installation Steps -->
                            <div class="relative isolate px-6 lg:px-8">
                                <div class="mx-auto max-w-2xl py-16">
                                    <div class="sm:mb-8 flex sm:justify-center">
                                        <div class="flex h-12 items-center justify-around">
                                            <!-- Logo -->
                                            <div class="shrink-0">
                                                <a href="https://www.wpcloudplugins.com" target="_blank" rel="noopener noreferrer">
                                                    <img class="h-16 w-auto" src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/wpcloudplugins-logo-dark.png" alt="WP Cloud Plugins">
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mx-auto max-w-2xl divide-y divide-gray-200 overflow-hidden rounded-lg bg-white shadow">
                                        <div class="px-4 py-5 sm:px-6">
                                            <h3 class="text-2xl font-semibold text-gray-900"><?php esc_html_e('Get Started', 'wpcloudplugins'); ?></h3>
                                        </div>
                                        <div class="px-4 py-5 sm:p-6">
                                            <?php AdminLayout::render_progress_bar($installation); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Installation Steps -->

                            <?php } else {?>

                            <!-- Hero Block -->
                            <div class="relative isolate px-6 pt-14 lg:px-8">
                                <div class="mx-auto max-w-2xl py-16">
                                    <div class="sm:mb-8 flex sm:justify-center">
                                        <div class="flex h-12 items-center justify-around">
                                            <!-- Logo -->
                                            <div class="shrink-0">
                                                <a href="https://www.wpcloudplugins.com" target="_blank" rel="noopener noreferrer">
                                                    <img class="h-16 w-auto" src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/wpcloudplugins-logo-dark.png" alt="WP Cloud Plugins">
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="hidden sm:mb-8 sm:flex sm:justify-center">
                                        <div class="relative rounded-full px-3 py-1 text-sm/6 text-gray-600 ring-1 ring-gray-900/10 hover:ring-gray-900/20">
                                            Version 3.0 introduces many changes. <a href="https://wpcloudplugins.gitbook.io/docs/other/changelog" class="wpcp-link-primary"><span class="absolute inset-0" aria-hidden="true"></span>Read more <span aria-hidden="true">&rarr;</span></a>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <p class="mt-8 text-pretty text-lg font-medium text-gray-500 sm:text-xl/8">The most powerful solution for displaying your cloud documents, images and media files in amazing ways! Designed for users with no coding experience.</p>
                                        <div class="mt-10 flex items-center justify-center gap-x-6">
                                            <a href="<?php echo admin_url('admin.php?page=OutoftheBox_settings_shortcodebuilder'); ?>" class="wpcp-button-primary">Manage Modules</a>
                                            <a href="https://wpcloudplugins.gitbook.io/docs/other/changelog" class="wpcp-button-secondary">Changelog <span aria-hidden="true">→</span></a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- End Hero Block -->

                            <?php } ?>

                            <!-- Blocks -->
                            <div class="gap-16 flex flex-col mx-auto max-w-5xl px-6 md:p-12">

                                <div class="relative">
                                    <div class="flex flex-col md:flex-row justify-center items-center gap-16">
                                        <div class="md:w-1/2">
                                            <h3 class="text-2xl font-bold">New Module Manager</h3>
                                            <p class="mt-1 text-lg text-gray-600"><strong>Managing the modules more efficiently</strong><br />We’re excited to introduce the much-requested <b>Module Manager</b> in version 3.0! This powerful new feature lets you manage all your modules across your site in a single, centralized location. <br /><br />You can now effortlessly view, organize, and control your modules in one place. It's a game-changer for managing the modules more efficiently and to make your workflow smoother and more efficient than ever. 🎉</p>
                                        </div>
                                        <div class="self-center md:w-1/2">
                                            <img class="w-full object-cover object-top shadow-2xl rounded-xl" src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/getting_started/module-manager.png" alt="">
                                        </div>
                                    </div>
                                </div>

                                <div class="relative">
                                    <div class="flex flex-col md:flex-row justify-center items-center gap-16">
                                        <div class="self-center md:w-1/2 order-last md:order-none">
                                            <img class="w-full object-cover object-top shadow-2xl rounded-xl" src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/getting_started/module-manager-2.png" alt="">
                                        </div>
                                        <div class="md:w-1/2">
                                            <h3 class="text-2xl font-bold">Brand-new Modules</h3>
                                            <p class="mt-1 text-lg text-gray-600">Version 3.0 also brings four brand-new module presets: <b>Review & Approve</b>, <b>Lists</b>, <b>Embed</b>, and <b>Button</b>! Whether you need a sleek download or preview list, a seamless file embed, or a quick action button, these presets save you time and effort while delivering professional results right out of the box.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="relative">
                                    <div class="flex flex-col md:flex-row justify-center items-center gap-16">
                                        <div class="md:w-1/2">
                                            <h3 class="text-2xl font-bold">Improved Module Configurator</h3>
                                            <p class="mt-1 text-lg text-gray-600">We’ve also given the Module Configuration interface a fresh redesign! The new layout is more intuitive and user-friendly, making it easier to fine-tune your modules to fit your needs.</p>
                                        </div>
                                        <div class="self-center md:w-1/2">
                                            <img class="w-full object-cover object-top shadow-2xl rounded-xl" src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/getting_started/module-configurator.png" alt="">
                                        </div>
                                    </div>
                                </div>


                                <div class="relative">
                                    <div class="flex flex-col md:flex-row justify-center items-center gap-16">
                                        <div class="self-center md:w-1/2">
                                            <img class="w-full object-cover object-top shadow-2xl rounded-xl" src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/getting_started/review-approve.png" alt="">
                                        </div>
                                        <div class="md:w-1/2">
                                            <h3 class="text-2xl font-bold">New "Review & Approve" Module</h3>
                                            <p class="mt-1 text-lg text-gray-600">We've added a powerful new Review & Approve module to the plugin! This feature allows users to create selections of images or documents, label them, and approve them with ease. Whether you're working with clients, team members, or legal documents, this module streamlines the review process and improves collaboration..</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="relative">
                                    <div class="flex flex-col md:flex-row justify-center items-center gap-16">
                                        <div class="md:w-1/2">
                                            <h3 class="text-2xl font-bold">Brand-New Documentation</h3>
                                            <p class="mt-1 text-lg text-gray-600">We’ve completely revamped our documentation from the ground up! The new, user-friendly design provides clearer explanations, step-by-step guides, and detailed examples to help you get the most out of the plugin. Whether you're a beginner or an advanced user, finding the information you need has never been easier!</p>
                                        </div>
                                        <div class="self-center md:w-1/2 order-last md:order-none">
                                            <img class="w-full object-cover object-top shadow-2xl rounded-xl" src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/getting_started/documentation.png" alt="">
                                        </div>
                                    </div>
                                </div>

                                <div class="relative">
                                    <div class="flex flex-col md:flex-row justify-center items-center gap-16">
                                        <div class="self-center md:w-1/2">
                                            <img class="w-full object-cover object-top shadow-2xl rounded-xl" src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/getting_started/media-library-importer.png" alt="">
                                        </div>
                                        <div class="md:w-1/2">
                                            <h3 class="text-2xl font-bold">Media Library Import</h3>
                                            <p class="mt-1 text-lg text-gray-600">With the new Add to Media Library button in the File Browser, you can now import cloud content directly into your Media Library and store the file locally on your site.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="relative">
                                    <div class="flex flex-col md:flex-row justify-center items-center gap-16">
                                        <div class="md:w-1/2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 mb-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                            </svg>
                                            <h3 class="text-2xl font-bold">Security</h3>
                                            <p class="mt-1 text-lg text-gray-600">We’ve strengthened your data security by upgrading token encryption to the AES-256-GCM encryption method, offering even more robust protection for sensitive information. Additionally, our plugin successfully passed its annual penetration tests, ensuring top-notch security and reliability for your site.</p>
                                        </div>
                                        <div class="md:w-1/2">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 mb-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                                            </svg>
                                            <h3 class="text-2xl font-bold">Beta Updates</h3>
                                            <p class="mt-1 text-lg text-gray-600">We’ve added a <b>Beta Updates</b> setting to the <b>Advanced tab</b> of the plugin options. Enable this setting to gain early access to beta updates and test new features before the official release. These updates are designed to be stable and let you stay ahead with the latest enhancements!</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="relative flex flex-col items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-16 mb-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                                    </svg>
                                    <h2 class="text-4xl font-bold">Video Tutorials</h2>
                                    <p class="mt-4 mb-4 text-lg text-gray-600 text-center">Learn how to use the plugin with ease by watching our detailed video tutorials. From setup to advanced configurations, these videos will guide you every step of the way. Check them out here!</p>
                                    <div class="mt-5 relative bg-brand-color-900 w-full aspect-video shadow-2xl rounded-3xl overflow-hidden">
                                        <iframe src='https://vimeo.com/showcase/11485861/embed' allowfullscreen loading="lazy" style='position:absolute;top:0;left:0;width:100%;height:100%;border:none' title="Video Instructions"></iframe>
                                    </div>
                                </div>

                                <div class="relative">
                                    <div class="flex flex-col md:flex-row justify-center items-center gap-16">
                                        <div class="md:w-1/2 bg-brand-color-100 backdrop-opacity-60 p-6 rounded-lg">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 mb-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                            </svg>
                                            <h3 class="text-2xl font-bold">Documentation</h3>
                                            <p class="mt-1 text-lg text-gray-600">Explore our comprehensive online documentation and video tutorials to uncover all the powerful features and possibilities of the plugin. Everything you need to know is just a click away!.</p>
                                            <a type="button" href='<?php echo OUTOFTHEBOX_ROOTPATH; ?>/_documentation/index.html' target="_blank" class="wpcp-button-secondary mt-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                                <?php esc_html_e('Open Documentation', 'wpcloudplugins'); ?>
                                            </a>
                                        </div>
                                        <div class="md:w-1/2 bg-brand-color-900/70 p-6 rounded-lg text-white">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 mb-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                            </svg>
                                            <h3 class="text-2xl font-bold text-white">Support</h3>
                                            <p class="mt-1 text-lg text-white">Need assistance? Our dedicated support team is here to provide you with fast, friendly, and top-notch support. We’re ready to help you make the most out of your plugin experience!</p>
                                            <a type="button" href='https://florisdeleeuwnl.zendesk.com/hc/en-us/articles/201845893' target="_blank" rel="noopener" class="wpcp-button-secondary mt-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="-ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                                </svg>
                                                <?php esc_html_e('Create support ticket', 'wpcloudplugins'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- End Blocks -->

                        </div>
                    </div>
                </main>
                <!-- End Page -->

            </div>

            <div class="fixed inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]" aria-hidden="true">
                <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#590E54] opacity-30 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
            </div>

            <div class="fixed bottom-8 right-8 -z-10 opacity-25" aria-hidden="true">
                <img class="object-contain h-24 lg:h-96" src="<?php echo OUTOFTHEBOX_ROOTPATH; ?>/css/images/dropbox_logo.svg" alt="">
            </div>

        </div>
    </div>
</div>