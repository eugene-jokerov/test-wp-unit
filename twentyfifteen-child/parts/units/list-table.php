<a href="<?php the_permalink(); ?>?act=new">Добавить юнит</a>
<table data-ajaxurl="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
	<thead>
		<th>Название</th>
		<th>Действия</th>
	</thead>
	<thead>
		<?php foreach ( $units as $unit ) : ?>
			<tr>
				<td><a target="_blank" href="<?php the_permalink( $unit->get_id() ); ?>"><?php echo esc_attr( $unit->get_title() ); ?></a></td>
				<td><a href="<?php the_permalink(); ?>?act=edit&unit_id=<?php echo esc_attr( $unit->get_id() ); ?>">Редактировать</a> | <a href="#" class="jwp-delete-unit" data-id="<?php echo esc_attr( $unit->get_id() ); ?>" data-nonce="<?php echo esc_attr( $unit->get_nonce() ); ?>">Удалить</a></td>
			</tr>
		<?php endforeach; ?>
	</thead>
</table>

