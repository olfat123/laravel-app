export const productRoute = (item) => {
    const options = {};
    Object.entries(item.option_ids || {}).forEach(([typeId, optionId]) => {
        options[typeId] = optionId;
    });

    return route('product.show', {
        product: item.slug,
        options: options
    });
};