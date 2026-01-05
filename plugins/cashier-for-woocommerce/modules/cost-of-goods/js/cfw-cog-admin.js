/**
 * Cost of Goods Admin JavaScript
 */

(function ($) {
	"use strict";

	var cfw_cog_admin = {
		toolTipElements: [],
		init: function () {
			this.initCogInput();
		},
		initCogInput: function () {
			const self         = cfw_cog_admin;
			let inputRelations = this.inputRelationals();
			if ( ! inputRelations.length > 0) {
				return;
			}
			for (let relation of inputRelations) {
				if (relation.initEvent) {
					$( "#woocommerce-product-data" ).on(
						relation.initEvent,
						function (event) {
							self.loadInput( relation );
						}
					);
				} else {
					self.loadInput( relation );
				}
			}
		},
		loadInput: function (relation) {
			$( relation.input ).on(
				"input",
				function () {
					cfw_cog_admin.validateCOG.call( this, relation );
				}
			);
		},
		validateCOG: function (field) {
			const self = cfw_cog_admin;
			if (field.targetInputs.length) {
				for (let targetInput of field.targetInputs) {
					if ( ! $( targetInput ).val()) {
						continue;
					}
					let isValid = self.compare(
						+$( this ).val(),
						+$( this ).closest( field.parent ).find( targetInput ).val(),
						field.relation
					);
					self.toggleError( $( this ), isValid );
					break;
				}
			}
		},
		toggleError( $input, isValid ) {
			$input.closest( "p" ).find( "span.cfw-error" ).remove();
			if ( ! isValid) {
				$input.addClass( "cfw-error" );
				$input
					.closest( "p" )
					.append(
						"<span class='cfw-error'>Cost of Good should be more than the product's selling price</span>"
					);
			} else {
				$input.removeClass( "cfw-error" );
			}
		},
		inputRelationals: function () {
			const inputRelations = [
				{
					targetInputs: [
						".options_group.pricing input#_sale_price",
						".options_group.pricing input#_regular_price",
					],
					input: ".options_group.sa_cfw_cog input#sa_cfw_cog_amount",
					parent: "#general_product_data",
					relation: "<=",
			},
				{
					targetInputs: [
						".variable_pricing input[id^='variable_sale_price']",
						".variable_pricing input[id^='variable_regular_price']",
					],
					input:
						".options_group.sa_cfw_cog input[id^='sa_cfw_cog_cost']",
					parent: ".woocommerce_variation.wc-metabox",
					relation: "<=",
					initEvent: "woocommerce_variations_loaded",
			},
			];
			return inputRelations;
		},
		compare: function (a, b, rel = "===") {
			switch (rel) {
				case "===":
					return a == b;
					break;
				case "!==":
					return a != b;
				case ">":
					return a > b;
					break;
				case "<":
					return a < b;
					break;
				case ">=":
					return a >= b;
					break;
				case "<=":
					return a <= b;
					break;
				default:
					return false;
					break;
			}
		},
	};

	cfw_cog_admin.init();
})( jQuery );
