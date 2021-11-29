"use strict";


import {CheckboxControl, PanelRow,TextareaControl} from "@wordpress/components";
import {withSelect, withDispatch} from "@wordpress/data";
import {createElement, Fragment} from "@wordpress/element";
import {withState, compose} from "@wordpress/compose";
import {addFilter} from "@wordpress/hooks";

class FSMCFIC_featured_image_caption extends React.Component {
    render() {
        const {
            meta,
			update_form_field,
            updateFSMCFIC_featured_image_caption,
            updateLegend,
            updateField,
        } = this.props;

        return (
            <>
				<hr/>
                <div>
					<TextareaControl
							label={FSMCFICL10n.featured_image_caption}
							help= {FSMCFICL10n.featured_image_caption_info}
							value={ meta._FSMCFIC_featured_image_caption }
							//className=''
							onChange={
                            (value) => {
								update_form_field('_FSMCFIC_featured_image_caption',value, meta,'text');
							}}
							
						/>
                </div>
				
                <PanelRow>
					 <CheckboxControl
						label={FSMCFICL10n.featured_image_no_caption}
                        checked={meta._FSMCFIC_featured_image_nocaption}
                        onChange={
                            (value) => {
                                this.setState({isChecked: value});
								update_form_field('_FSMCFIC_featured_image_nocaption',value, meta,'check');
                            }
                        }
						/>
                </PanelRow>
                <PanelRow>
					 <CheckboxControl
						label={FSMCFICL10n.featured_image_hide}
                        checked={meta._FSMCFIC_featured_image_hide}
                        onChange={
                            (value) => {
                                this.setState({isChecked: value});
								 update_form_field('_FSMCFIC_featured_image_hide',value, meta,'check');
                            }
                        }
						/>
                </PanelRow>
            </>
        )
    }
}

const composedFSMCFIC_featured_image_caption = compose([
    withState((value) => value),
    withSelect((select) => {
        const currentMeta = select('core/editor').getCurrentPostAttribute('meta');
        const editedMeta = select('core/editor').getEditedPostAttribute('meta');
        return {
            meta: {...currentMeta, ...editedMeta},
        };
    }),
    withDispatch((dispatch) => ({
		
		update_form_field(field,value,meta,type)
		{
			if (type == 'check') { value = value ? '1': '';}
            meta = {
                ...meta,
                [field]: value,
            };
            dispatch('core/editor').editPost({meta});
			
			
		},
		
		
    })),
])(FSMCFIC_featured_image_caption);

const wrapPostFeaturedImage = function (OriginalComponent) {
    return function (props) {
        return (
            createElement(
                Fragment,
                {},
                null,
                createElement(
                    OriginalComponent,
                    props
                ),
                createElement(
                    composedFSMCFIC_featured_image_caption
                )
            )
        );
    }
};

addFilter(
    'editor.PostFeaturedImage',
    'FSMFIC/addControl',
    wrapPostFeaturedImage
);