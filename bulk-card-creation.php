<?php
/**
 * Plugin Name: Bulk Card Creation and Editing
 * Description: Criação e edição de cartões NFC em massa a partir de ficheiros CSV, com suporte completo ao ACF e Elementor Pro.
 * Version: 2.0
 * Author: The Wild Theory
 */

if (!defined('ABSPATH')) {
    exit;
}

// Registra o menu no WordPress Admin
add_action('admin_menu', 'bulk_card_creation_menu');
function bulk_card_creation_menu() {
    add_menu_page(
        'Bulk Card Creation and Editing',
        'Bulk Card Creation',
        'manage_options',
        'bulk-card-creation',
        'bulk_card_creation_page',
        'dashicons-id',
        20
    );
}

// Página do plugin
function bulk_card_creation_page() {
    ?>
    <div class="wrap">
        <h1>Criação e Edição de Cartões em Massa</h1>

        <h2>Descarregar Modelo CSV</h2>
        <p>Descarrega o modelo CSV com os campos do ACF configurados no site.</p>
        <form method="GET" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="generate_csv_template">
            <button type="submit" class="button button-secondary">Descarregar Modelo CSV</button>
        </form>
        <hr>

        <h2>Descarregar Dados dos Post Types</h2>
        <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="export_post_types">
            <label for="post_types">Selecionar Post Types:</label><br>
            <select name="post_types[]" id="post_types" multiple required>
                <?php
                $post_types = get_post_types(['public' => true], 'objects');
                foreach ($post_types as $post_type) {
                    echo '<option value="' . esc_attr($post_type->name) . '">' . esc_html($post_type->label) . '</option>';
                }
                ?>
            </select><br><br>
            <button type="submit" class="button button-secondary">Descarregar Dados</button>
        </form>
        <hr>

        <h2>Importar Ficheiro CSV</h2>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="bulk_card_creation">
            <label for="csv_file">Carregar ficheiro CSV:</label><br>
            <input type="file" name="csv_file" id="csv_file" accept=".csv" required><br><br>
            <label for="post_type">Selecionar Post Type:</label><br>
            <select name="post_type" id="post_type" required>
                <?php
                $post_types = get_post_types(['public' => true], 'objects');
                foreach ($post_types as $post_type) {
                    echo '<option value="' . esc_attr($post_type->name) . '">' . esc_html($post_type->label) . '</option>';
                }
                ?>
            </select><br><br>
            <button type="submit" class="button button-primary">Criar/Atualizar Cartões</button>
        </form>
    </div>
    <?php
}

// Gerar modelo CSV dinamicamente
add_action('admin_post_generate_csv_template', 'generate_csv_template');
function generate_csv_template() {
    if (!current_user_can('manage_options')) {
        wp_die('Não tens permissões suficientes para realizar esta ação.');
    }

    // Lista de campos ACF
    $acf_fields = [
        'cor_de_fundo',
        'Cor_de_Caixa_de_Texto',
        'Cor_Titulos',
        'Cor_Texto',
        'cor_borda_social_icons',
        'cor_texto_botoes_icons',
        'cor_fundo_botoes_icons',
        'cor_borda_botoes_icons',
        'post_title',
        'name',
        'position',
        'company_name',
        'polo',
        'contact',
        'whatsapp_link',
        'e-mail',
        'company_e_mail',
        'company_contact',
        'customer_care',
        'website',
        'addess',
        'postal_code',
        'cidade',
        'distrito',
        'pais',
        'direcoes',
        'linkdein_link',
        'instagram_link',
        'facebook_link',
        'x_twitter_link',
        'youtube',
        'tik_tok',
        'featured_image',
        'cover_photo',
        'logo_photo',
    ];

    // Criar ficheiro CSV temporário
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="acf_template.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, $acf_fields);
    fclose($output);
    exit;
}

// Exportar dados dos post types selecionados
add_action('admin_post_export_post_types', 'export_post_types');
function export_post_types() {
    if (!current_user_can('manage_options')) {
        wp_die('Não tens permissões suficientes para realizar esta ação.');
    }

    if (isset($_POST['post_types'])) {
        $post_types = $_POST['post_types'];
        $acf_fields = [
            'cor_de_fundo',
            'Cor_de_Caixa_de_Texto',
            'Cor_Titulos',
            'Cor_Texto',
            'cor_borda_social_icons',
            'cor_texto_botoes_icons',
            'cor_fundo_botoes_icons',
            'cor_borda_botoes_icons',
            'post_title',
            'name',
            'position',
            'company_name',
            'polo',
            'contact',
            'whatsapp_link',
            'e-mail',
            'company_e_mail',
            'company_contact',
            'customer_care',
            'website',
            'addess',
            'postal_code',
            'cidade',
            'distrito',
            'pais',
            'direcoes',
            'linkdein_link',
            'instagram_link',
            'facebook_link',
            'x_twitter_link',
            'youtube',
            'tik_tok',
            'featured_image',
            'cover_photo',
            'logo_photo',
        ];

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="post_types_data.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, $acf_fields);

        foreach ($post_types as $post_type) {
            $posts = get_posts([
                'post_type' => $post_type,
                'posts_per_page' => -1,
                'post_status' => 'publish',
            ]);

            foreach ($posts as $post) {
                $row = [];
                foreach ($acf_fields as $field) {
                    if ($field == 'featured_image') {
                        $image_id = get_post_thumbnail_id($post->ID);
                        $row[$field] = wp_get_attachment_url($image_id);
                    } elseif (in_array($field, ['cover_photo', 'logo_photo'])) {
                        $image_id = get_post_meta($post->ID, $field, true);
                        $row[$field] = wp_get_attachment_url($image_id);
                    } else {
                        $row[$field] = get_post_meta($post->ID, $field, true);
                    }
                }
                // Adicionar o título do post ao campo post_title
                $row['post_title'] = get_the_title($post->ID);
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    }

    wp_redirect(add_query_arg('bulk_status', 'error', admin_url('admin.php?page=bulk-card-creation')));
    exit;
}

// Processamento do CSV (continuação)
add_action('admin_post_bulk_card_creation', 'process_bulk_card_creation');
function process_bulk_card_creation() {
    if (!current_user_can('manage_options')) {
        wp_die('Não tens permissões suficientes para realizar esta ação.');
    }

    if (isset($_FILES['csv_file']) && isset($_POST['post_type'])) {
        $csv_file = $_FILES['csv_file']['tmp_name'];
        $current_user_id = get_current_user_id();
        $selected_post_type = sanitize_text_field($_POST['post_type']);

        if (($handle = fopen($csv_file, "r")) !== false) {
            $header = fgetcsv($handle, 1000, ","); // Lê os cabeçalhos do CSV

            // Verifica se o cabeçalho foi lido corretamente
            if (!$header) {
                error_log('Erro ao ler o cabeçalho do CSV.');
                wp_redirect(add_query_arg('bulk_status', 'error', admin_url('admin.php?page=bulk-card-creation')));
                exit;
            }

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                // Verifica se o número de elementos no cabeçalho e na linha de dados são iguais
                if (count($header) !== count($data)) {
                    error_log('Número de elementos no cabeçalho e na linha de dados não são iguais.');
                    continue;
                }

                $row_data = array_combine($header, $data);

                // Verifica se o campo "post_title" não está vazio
                if (empty($row_data['post_title'])) {
                    error_log('Campo "post_title" está vazio.');
                    continue;
                }

                // Verifica se o post já existe usando WP_Query
                $existing_post = new WP_Query([
                    'post_type' => $selected_post_type,
                    'title' => $row_data['post_title'],
                    'posts_per_page' => 1,
                ]);

                if ($existing_post->have_posts()) {
                    $existing_post->the_post();
                    $post_id = get_the_ID();
                    error_log("Post existente encontrado: ID $post_id, Título: " . $row_data['post_title']);
                } else {
                    // Cria o post se não existir
                    $post_id = wp_insert_post(array(
                        'post_title'   => $row_data['post_title'],
                        'post_type'    => $selected_post_type,
                        'post_status'  => 'publish',
                        'post_author'  => $current_user_id,
                        'meta_input'   => array(
                            '_elementor_template_type' => 'default', // Define o template como Elementor Default
                        ),
                    ));

                    if (is_wp_error($post_id)) {
                        error_log("Erro ao criar post: " . $post_id->get_error_message());
                        continue;
                    }

                    error_log("Post criado: ID $post_id, Título: " . $row_data['post_title']);
                }

                // Atualiza os campos ACF usando as chaves dos campos
                if ($post_id) {
                    foreach ($row_data as $key => $value) {
                        if (!empty($key) && $key != 'post_title') {
                            $field_key = get_acf_field_key($key, $selected_post_type);
                            if ($field_key) {
                                update_field($field_key, $value, $post_id);
                                error_log("Campo ACF atualizado: $key => $value, Post ID: $post_id");
                            } else {
                                add_field_to_acf($key, $selected_post_type); // Adiciona o campo se não existir
                                $field_key = get_acf_field_key($key, $selected_post_type);
                                if ($field_key) {
                                    update_field($field_key, $value, $post_id);
                                    error_log("Campo ACF adicionado e atualizado: $key => $value, Post ID: $post_id");
                                }
                            }
                        }
                    }

                    // Define as imagens dos campos ACF
                    update_acf_image_field($post_id, 'featured_image', $row_data['featured_image']);
                    update_acf_image_field($post_id, 'cover_photo', $row_data['cover_photo']);
                    update_acf_image_field($post_id, 'logo_photo', $row_data['logo_photo']);

                    // Define o post como template do Elementor
                    update_post_meta($post_id, '_elementor_template_type', 'default');
                } else {
                    error_log("Erro ao criar ou atualizar o post: Título: " . $row_data['post_title']);
                }

                // Reseta postdata
                wp_reset_postdata();
            }

            fclose($handle);
            wp_redirect(add_query_arg('bulk_status', 'success', admin_url('admin.php?page=bulk-card-creation')));
            exit;
        } else {
            error_log('Erro ao abrir o arquivo CSV.');
        }
    } else {
        error_log('Arquivo CSV ou post_type não definido.');
    }

    wp_redirect(add_query_arg('bulk_status', 'error', admin_url('admin.php?page=bulk-card-creation')));
    exit;
}

// Função para obter a chave do campo ACF
function get_acf_field_key($field_name, $post_type) {
    $field_groups = acf_get_field_groups(['post_type' => $post_type]);
    foreach ($field_groups as $group) {
        $fields = acf_get_fields($group['key']);
        foreach ($fields as $field) {
            if ($field['name'] === $field_name) {
                return $field['key'];
            }
        }
    }
    return null;
}

// Função para adicionar dinamicamente campos ao ACF
function add_field_to_acf($field_name, $post_type) {
    $group_key = 'group_' . uniqid();
    $field_key = 'field_' . uniqid();

    acf_add_local_field_group([
        'key' => $group_key,
        'title' => 'Campos Automáticos',
        'fields' => [
            [
                'key' => $field_key,
                'label' => ucfirst(str_replace('_', ' ', $field_name)),
                'name' => $field_name,
                'type' => 'text',
            ]
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => $post_type,
                ],
            ],
        ],
    ]);
}

// Função para atualizar campos de imagem ACF e definir imagem destacada
function update_acf_image_field($post_id, $field_name, $image_url) {
    if (!empty($image_url)) {
        $image_id = attachment_url_to_postid($image_url);
        if ($image_id) {
            if ($field_name == 'featured_image') {
                set_post_thumbnail($post_id, $image_id);
                error_log("Imagem destacada definida: $image_url, Post ID: $post_id");
            } else {
                $field_key = get_acf_field_key($field_name, get_post_type($post_id));
                if ($field_key) {
                    update_field($field_key, $image_id, $post_id);
                    error_log("Campo de imagem ACF atualizado: $field_name => $image_url, Post ID: $post_id");
                }
            }
        } else {
            error_log("Imagem não encontrada: $image_url");
        }
    }
}